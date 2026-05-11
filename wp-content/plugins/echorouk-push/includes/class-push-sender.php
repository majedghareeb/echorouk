<?php
/**
 * Builds and delivers RFC 8030 Web Push HTTP requests.
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_Sender {

    /** Default TTL in seconds (24 hours). */
    const DEFAULT_TTL = 86400;

    /**
     * Send a Web Push notification to a single subscription.
     *
     * @param object $sub  Subscription row from DB (endpoint, p256dh, auth).
     * @param array  $notification  Keys: title, body, icon, badge, url, image.
     * @return bool  True on success, false on failure.
     */
    public static function send(object $sub, array $notification): bool {
        $payload = json_encode([
            'title'  => $notification['title']  ?? '',
            'body'   => $notification['body']   ?? '',
            'icon'   => $notification['icon']   ?? '',
            'badge'  => $notification['badge']  ?? '',
            'url'    => $notification['url']    ?? home_url('/'),
            'image'  => $notification['image']  ?? '',
        ]);

        // Encrypt payload
        try {
            $encrypted = Echorouk_Push_Crypto::encrypt($payload, $sub->p256dh, $sub->auth);
        } catch (Exception $e) {
            error_log('[echorouk-push] Encryption error for ' . $sub->endpoint . ': ' . $e->getMessage());
            return false;
        }

        // Build the binary header for aes128gcm (RFC 8188)
        // Header: salt (16) | rs (4 BE) | idlen (1) | keyid (server public key, 65)
        $rs     = pack('N', 4096); // record size
        $idlen  = chr(65);
        $header = $encrypted['salt'] . $rs . $idlen . $encrypted['server_public'];

        $body = $header . $encrypted['ciphertext'];

        // VAPID Authorization header
        $endpoint_origin = self::get_origin($sub->endpoint);
        $auth_header     = Echorouk_Push_VAPID::get_auth_header($endpoint_origin);

        if (empty($auth_header)) {
            error_log('[echorouk-push] Could not build VAPID header.');
            return false;
        }

        $response = wp_remote_post($sub->endpoint, [
            'timeout'    => 20,
            'blocking'   => true,
            'headers'    => [
                'Authorization'   => $auth_header,
                'Content-Type'    => 'application/octet-stream',
                'Content-Encoding'=> 'aes128gcm',
                'TTL'             => (string) self::DEFAULT_TTL,
                'Urgency'         => 'normal',
            ],
            'body'       => $body,
        ]);

        if (is_wp_error($response)) {
            error_log('[echorouk-push] WP HTTP error: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Subscription gone — remove it
        if ($code === 404 || $code === 410) {
            Echorouk_Push_Subscription_DB::remove_by_endpoint($sub->endpoint);
            return false;
        }

        if ($code < 200 || $code >= 300) {
            error_log(sprintf(
                '[echorouk-push] HTTP %d for %s | Response: %s',
                $code,
                $sub->endpoint,
                substr($body, 0, 500)
            ));
            return false;
        }

        return true;
    }

    /**
     * Broadcast a notification to ALL active subscribers.
     * Returns: ['sent' => int, 'failed' => int, 'removed' => int]
     */
    public static function broadcast(array $notification): array {
        $subs   = Echorouk_Push_Subscription_DB::get_all_active();
        $stats  = ['sent' => 0, 'failed' => 0, 'total' => count($subs)];

        foreach ($subs as $sub) {
            if (self::send($sub, $notification)) {
                $stats['sent']++;
            } else {
                $stats['failed']++;
            }
            // Tiny sleep to avoid hammering push servers
            usleep(20000); // 20ms
        }

        return $stats;
    }

    /** Extract the origin (scheme + host) from a push endpoint URL. */
    private static function get_origin(string $url): string {
        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host']   ?? '';
        $port   = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        return "$scheme://$host$port";
    }
}
