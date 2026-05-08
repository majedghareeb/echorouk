<?php
/**
 * VAPID key management and JWT signing for Web Push (RFC 8292).
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_VAPID {

    const OPT_PRIVATE = 'echorouk_push_vapid_private';
    const OPT_PUBLIC  = 'echorouk_push_vapid_public';

    /**
     * Generate and store a P-256 VAPID key pair if not already present.
     */
    public static function generate_keys_if_missing(): void {
        if (get_option(self::OPT_PRIVATE)) {
            return;
        }

        $key = openssl_pkey_new([
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        if (! $key) {
            error_log('[echorouk-push] Failed to generate VAPID keys: ' . openssl_error_string());
            return;
        }

        $details = openssl_pkey_get_details($key);

        // Uncompressed public key: 0x04 || x (32 bytes) || y (32 bytes)
        $pub = "\x04"
            . str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT)
            . str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT);

        // Private scalar d (32 bytes)
        $priv = str_pad($details['ec']['d'], 32, "\x00", STR_PAD_LEFT);

        update_option(self::OPT_PUBLIC,  self::b64u_encode($pub),  false);
        update_option(self::OPT_PRIVATE, self::b64u_encode($priv), false);
    }

    /** Returns the base64url-encoded uncompressed public key (for browser subscription). */
    public static function get_public_key(): string {
        return (string) get_option(self::OPT_PUBLIC, '');
    }

    /** Returns the base64url-encoded raw private scalar. */
    public static function get_private_key(): string {
        return (string) get_option(self::OPT_PRIVATE, '');
    }

    /**
     * Build and return the VAPID Authorization header value for the given endpoint origin.
     * Format: "vapid t=<jwt>,k=<public_key_base64url>"
     */
    public static function get_auth_header(string $endpoint_origin): string {
        $header  = self::b64u_encode((string) json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $payload = self::b64u_encode((string) json_encode([
            'aud' => $endpoint_origin,
            'exp' => time() + 43200,
            'sub' => 'mailto:push@echorouk.net',
        ]));

        $input       = "$header.$payload";
        $private_key = self::load_private_key_pem();

        if (! $private_key) {
            error_log('[echorouk-push] Cannot load VAPID private key for signing.');
            return '';
        }

        openssl_sign($input, $der_sig, $private_key, OPENSSL_ALGO_SHA256);
        $raw_sig = self::der_to_raw($der_sig);

        $jwt = $input . '.' . self::b64u_encode($raw_sig);
        return 'vapid t=' . $jwt . ',k=' . self::get_public_key();
    }

    /** Load the stored private key as an OpenSSL key resource via PEM. */
    private static function load_private_key_pem() {
        $priv_raw = self::b64u_decode(self::get_private_key());
        $pub_raw  = self::b64u_decode(self::get_public_key());

        if (strlen($priv_raw) !== 32 || strlen($pub_raw) !== 65) {
            return false;
        }

        $x = substr($pub_raw, 1, 32);
        $y = substr($pub_raw, 33, 32);

        // Build EC private key DER (RFC 5915)
        $ec_private_key_der = self::build_ec_private_der($priv_raw, $x, $y);
        $pem = "-----BEGIN EC PRIVATE KEY-----\n"
             . chunk_split(base64_encode($ec_private_key_der), 64, "\n")
             . "-----END EC PRIVATE KEY-----\n";

        return openssl_pkey_get_private($pem);
    }

    /**
     * Build RFC 5915 EC private key DER for P-256.
     * SEQUENCE {
     *   version INTEGER (1),
     *   privateKey OCTET STRING,
     *   [0] OBJECT IDENTIFIER (prime256v1),
     *   [1] BIT STRING (uncompressed public key)
     * }
     */
    private static function build_ec_private_der(string $d, string $x, string $y): string {
        $version     = "\x02\x01\x01"; // INTEGER 1
        $private_key = "\x04\x20" . $d; // OCTET STRING (32 bytes)

        // OID for prime256v1: 1.2.840.10045.3.1.7
        $oid         = "\xa0\x0a\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";

        // Public key as BIT STRING (0x00 || uncompressed point)
        $pub_point   = "\x04" . $x . $y;
        $bit_string  = "\x00" . $pub_point; // bit-string, 0 unused bits
        $pub_ber     = "\xa1" . self::der_length(strlen($bit_string) + 2)
                     . "\x03" . self::der_length(strlen($bit_string)) . $bit_string;

        $body = $version . $private_key . $oid . $pub_ber;
        return "\x30" . self::der_length(strlen($body)) . $body;
    }

    /** Encode a DER length value. */
    private static function der_length(int $len): string {
        if ($len < 128) {
            return chr($len);
        }
        $bytes = '';
        $tmp = $len;
        while ($tmp > 0) {
            $bytes = chr($tmp & 0xff) . $bytes;
            $tmp >>= 8;
        }
        return chr(0x80 | strlen($bytes)) . $bytes;
    }

    /**
     * Convert DER-encoded ECDSA signature to raw R||S (64 bytes) for JWT ES256.
     */
    private static function der_to_raw(string $der): string {
        $offset = 2;
        // Handle long-form length
        if (strlen($der) > 1 && (ord($der[1]) & 0x80)) {
            $offset += ord($der[1]) & 0x7f;
        }

        // R
        $offset++; // skip 0x02 tag
        $r_len = ord($der[$offset++]);
        $r = substr($der, $offset, $r_len);
        $offset += $r_len;

        // S
        $offset++; // skip 0x02 tag
        $s_len = ord($der[$offset++]);
        $s = substr($der, $offset, $s_len);

        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    public static function b64u_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function b64u_decode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
