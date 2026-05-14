<?php
/**
 * RFC 8291 Web Push payload encryption (aes128gcm content encoding).
 *
 * Requires PHP 7.3+ (openssl_pkey_derive) and OpenSSL with P-256 support.
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_Crypto {

    /**
     * Encrypt a plaintext string for delivery to a push subscription.
     *
     * @param string $plaintext        The notification JSON payload.
     * @param string $p256dh_b64u     Subscriber's P-256 public key (base64url).
     * @param string $auth_b64u       Subscriber's auth secret (base64url).
     * @return array{
     *   ciphertext: string,
     *   salt: string,
     *   server_public: string
     * }
     * @throws RuntimeException on crypto failure.
     */
    public static function encrypt(string $plaintext, string $p256dh_b64u, string $auth_b64u): array {
        $client_public = self::b64u_decode($p256dh_b64u);
        $auth_secret   = self::b64u_decode($auth_b64u);

        if (strlen($client_public) !== 65 || $client_public[0] !== "\x04") {
            throw new RuntimeException('[echorouk-push] Invalid p256dh key length.');
        }

        // Generate ephemeral server ECDH key pair
        $server_key = openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);
        if (! $server_key) {
            throw new RuntimeException('[echorouk-push] Failed to generate ephemeral key: ' . openssl_error_string());
        }

        $server_details    = openssl_pkey_get_details($server_key);
        $server_public_raw = "\x04"
            . str_pad($server_details['ec']['x'], 32, "\x00", STR_PAD_LEFT)
            . str_pad($server_details['ec']['y'], 32, "\x00", STR_PAD_LEFT);

        // Import client public key
        $client_pem = self::p256_pub_to_pem($client_public);
        $client_key = openssl_pkey_get_public($client_pem);
        if (! $client_key) {
            throw new RuntimeException('[echorouk-push] Failed to import client public key.');
        }

        // ECDH shared secret (requires PHP 7.3+)
        $shared_secret = openssl_pkey_derive($client_key, $server_key, 32);
        if ($shared_secret === false) {
            throw new RuntimeException('[echorouk-push] ECDH failed: ' . openssl_error_string());
        }

        $salt = random_bytes(16);

        // IKM via HKDF using auth_secret as salt
        // info = "WebPush: info\x00" || client_public || server_public
        $info = "WebPush: info\x00" . $client_public . $server_public_raw;
        $ikm  = self::hkdf($auth_secret, $shared_secret, $info, 32);

        // RFC 8291 / RFC 8188:
        // PRK = HKDF-Extract(salt, IKM)
        // CEK = HKDF-Expand(PRK, "Content-Encoding: aes128gcm\x00", 16)
        // NONCE = HKDF-Expand(PRK, "Content-Encoding: nonce\x00", 12)
        $prk   = hash_hmac('sha256', $ikm, $salt, true);
        $cek   = self::hkdf_expand($prk, "Content-Encoding: aes128gcm\x00", 16);
        $nonce = self::hkdf_expand($prk, "Content-Encoding: nonce\x00", 12);

        // Pad plaintext: content || \x02 (delimiter, no padding)
        $padded = $plaintext . "\x02";

        // AES-128-GCM encryption
        $tag        = '';
        $ciphertext = openssl_encrypt($padded, 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
        if ($ciphertext === false) {
            throw new RuntimeException('[echorouk-push] AES-GCM encryption failed.');
        }
        $ciphertext .= $tag;

        return [
            'ciphertext'    => $ciphertext,
            'salt'          => $salt,
            'server_public' => $server_public_raw,
        ];
    }

    /**
     * HKDF-SHA-256 (Extract + Expand).
     */
    private static function hkdf(string $salt, string $ikm, string $info, int $length): string {
        // Extract
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        return self::hkdf_expand($prk, $info, $length);
    }

    /**
     * HKDF-Expand for a single block (length <= 32).
     */
    private static function hkdf_expand(string $prk, string $info, int $length): string {
        return substr(hash_hmac('sha256', $info . "\x01", $prk, true), 0, $length);
    }

    /**
     * Wrap a raw uncompressed P-256 public key in a SubjectPublicKeyInfo PEM.
     */
    private static function p256_pub_to_pem(string $raw): string {
        // SubjectPublicKeyInfo for id-ecPublicKey / prime256v1
        $spki_header = "\x30\x59"                      // SEQUENCE (89 bytes)
            . "\x30\x13"                                // SEQUENCE (19 bytes) AlgorithmIdentifier
            . "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01"  // OID id-ecPublicKey
            . "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07" // OID prime256v1
            . "\x03\x42\x00"                            // BIT STRING (66 bytes, 0 unused)
            . $raw;

        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($spki_header), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    public static function b64u_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function b64u_decode(string $data): string {
        $base64 = strtr($data, '-_', '+/');
        $pad    = strlen($base64) % 4;
        if ($pad > 0) {
            $base64 .= str_repeat('=', 4 - $pad);
        }

        $decoded = base64_decode($base64, true);
        return false === $decoded ? '' : $decoded;
    }
}
