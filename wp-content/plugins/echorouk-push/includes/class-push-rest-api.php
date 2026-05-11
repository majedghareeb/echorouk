<?php
/**
 * REST API endpoints for push subscription management.
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_REST_API {

    private static $instance = null;
    const NAMESPACE = 'echorouk-push/v1';

    public static function instance(): self {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
        // GET public VAPID key (unauthenticated)
        register_rest_route(self::NAMESPACE, '/vapid-public-key', [
            'methods'             => 'GET',
            'callback'            => [$this, 'get_public_key'],
            'permission_callback' => '__return_true',
        ]);

        // POST subscribe
        register_rest_route(self::NAMESPACE, '/subscribe', [
            'methods'             => 'POST',
            'callback'            => [$this, 'subscribe'],
            'permission_callback' => '__return_true',
            'args'                => [
                'endpoint'   => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
                'p256dh'     => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'auth'       => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'browser'    => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            ],
        ]);

        // DELETE unsubscribe
        register_rest_route(self::NAMESPACE, '/unsubscribe', [
            'methods'             => 'DELETE',
            'callback'            => [$this, 'unsubscribe'],
            'permission_callback' => '__return_true',
            'args'                => [
                'endpoint' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
            ],
        ]);

        // POST send (admin only)
        register_rest_route(self::NAMESPACE, '/send', [
            'methods'             => 'POST',
            'callback'            => [$this, 'send_notification'],
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
            'args'                => [
                'title' => ['required' => true,  'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'body'  => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
                'url'   => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
                'icon'  => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
                'badge' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
                'image' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'esc_url_raw'],
            ],
        ]);

        // GET diagnostics (admin only)
        register_rest_route(self::NAMESPACE, '/diagnostics', [
            'methods'             => 'GET',
            'callback'            => [$this, 'diagnostics'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        // POST test-send to one subscriber (admin only)
        register_rest_route(self::NAMESPACE, '/test-send', [
            'methods'             => 'POST',
            'callback'            => [$this, 'test_send'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);

        // DELETE clear all subscriptions (admin only)
        register_rest_route(self::NAMESPACE, '/clear-subscriptions', [
            'methods'             => 'DELETE',
            'callback'            => [$this, 'clear_subscriptions'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ]);
    }

    public function get_public_key(): WP_REST_Response {
        return new WP_REST_Response(['key' => Echorouk_Push_VAPID::get_public_key()], 200);
    }

    public function subscribe(WP_REST_Request $req): WP_REST_Response {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $saved = Echorouk_Push_Subscription_DB::save([
            'endpoint'   => $req->get_param('endpoint'),
            'p256dh'     => $req->get_param('p256dh'),
            'auth'       => $req->get_param('auth'),
            'browser'    => $req->get_param('browser') ?: self::detect_browser($ua),
            'user_agent' => substr($ua, 0, 512),
        ]);

        if (! $saved) {
            return new WP_REST_Response(['error' => 'Could not save subscription'], 500);
        }

        return new WP_REST_Response([
            'success' => true,
            'count'   => Echorouk_Push_Subscription_DB::count_active(),
        ], 201);
    }

    public function unsubscribe(WP_REST_Request $req): WP_REST_Response {
        Echorouk_Push_Subscription_DB::delete_by_endpoint($req->get_param('endpoint'));
        return new WP_REST_Response(['success' => true], 200);
    }

    public function send_notification(WP_REST_Request $req): WP_REST_Response {
        $notification = [
            'title' => $req->get_param('title'),
            'body'  => $req->get_param('body')  ?? '',
            'url'   => $req->get_param('url')   ?? home_url('/'),
            'icon'  => $req->get_param('icon')  ?? '',
            'badge' => $req->get_param('badge') ?? '',
            'image' => $req->get_param('image') ?? '',
        ];

        $stats = Echorouk_Push_Sender::broadcast($notification);

        // Log the send
        $log = get_option('echorouk_push_send_log', []);
        array_unshift($log, array_merge($notification, [
            'stats' => $stats,
            'time'  => current_time('mysql', true),
        ]));
        update_option('echorouk_push_send_log', array_slice($log, 0, 50));

        return new WP_REST_Response($stats, 200);
    }

    /** GET /diagnostics — server capability check */
    public function diagnostics(): WP_REST_Response {
        $public_key   = Echorouk_Push_VAPID::get_public_key();
        $sw_exists    = file_exists(ABSPATH . 'echorouk-push-sw.js');
        $sub_count    = Echorouk_Push_Subscription_DB::count_active();
        $all_subs     = Echorouk_Push_Subscription_DB::get_all_active();
        $first_sub    = !empty($all_subs) ? $all_subs[0] : null;

        // Test VAPID key loading
        $vapid_pem_ok = false;
        $vapid_error  = '';
        if ($public_key) {
            try {
                $hdr = Echorouk_Push_VAPID::get_auth_header('https://fcm.googleapis.com');
                $vapid_pem_ok = !empty($hdr);
            } catch (Exception $e) {
                $vapid_error = $e->getMessage();
            }
        }

        return new WP_REST_Response([
            'php_version'           => PHP_VERSION,
            'openssl_version'       => OPENSSL_VERSION_TEXT,
            'pkey_derive_available' => function_exists('openssl_pkey_derive'),
            'gcm_available'         => in_array('aes-128-gcm', openssl_get_cipher_methods(), true),
            'vapid_key_set'         => !empty($public_key),
            'vapid_public_key'      => $public_key,
            'vapid_jwt_ok'          => $vapid_pem_ok,
            'vapid_error'           => $vapid_error,
            'sw_deployed'           => $sw_exists,
            'sw_url'                => home_url('/echorouk-push-sw.js'),
            'subscriber_count'      => $sub_count,
            'first_endpoint_origin' => $first_sub ? self::get_origin_static($first_sub->endpoint) : null,
        ], 200);
    }

    /** POST /test-send — send to the first stored subscription, return raw push server response */
    public function test_send(): WP_REST_Response {
        $subs = Echorouk_Push_Subscription_DB::get_all_active();
        if (empty($subs)) {
            return new WP_REST_Response(['error' => 'No active subscriptions found.'], 404);
        }

        $sub = $subs[0];
        $notification = [
            'title' => 'Test Push — Echorouk',
            'body'  => 'هذا اختبار للإشعارات',
            'url'   => home_url('/'),
            'icon'  => get_option('echorouk_push_icon_url', ''),
            'badge' => get_option('echorouk_push_badge_url', ''),
            'image' => '',
        ];

        $payload = json_encode($notification);

        // Encrypt
        try {
            $encrypted = Echorouk_Push_Crypto::encrypt($payload, $sub->p256dh, $sub->auth);
        } catch (Exception $e) {
            return new WP_REST_Response(['error' => 'Encryption failed: ' . $e->getMessage()], 500);
        }

        // Build body
        $rs     = pack('N', 4096);
        $header = $encrypted['salt'] . $rs . chr(65) . $encrypted['server_public'];
        $body   = $header . $encrypted['ciphertext'];

        $endpoint_origin = self::get_origin_static($sub->endpoint);
        $auth_header     = Echorouk_Push_VAPID::get_auth_header($endpoint_origin);

        if (empty($auth_header)) {
            return new WP_REST_Response(['error' => 'VAPID signing failed — check server error log.'], 500);
        }

        $response = wp_remote_post($sub->endpoint, [
            'timeout' => 20,
            'blocking'=> true,
            'headers' => [
                'Authorization'    => $auth_header,
                'Content-Type'     => 'application/octet-stream',
                'Content-Encoding' => 'aes128gcm',
                'TTL'              => '86400',
                'Urgency'          => 'normal',
            ],
            'body' => $body,
        ]);

        if (is_wp_error($response)) {
            return new WP_REST_Response([
                'error'    => 'HTTP request failed',
                'detail'   => $response->get_error_message(),
                'endpoint' => $sub->endpoint,
            ], 500);
        }

        $code      = wp_remote_retrieve_response_code($response);
        $resp_body = wp_remote_retrieve_body($response);
        $raw_hdrs  = wp_remote_retrieve_headers($response);
        // getAll() exists on WP Requests dictionary objects; fall back to array cast
        $resp_hdrs = is_object($raw_hdrs) && method_exists($raw_hdrs, 'getAll')
            ? $raw_hdrs->getAll()
            : (array) $raw_hdrs;

        return new WP_REST_Response([
            'endpoint'           => $sub->endpoint,
            'endpoint_origin'    => $endpoint_origin,
            'http_status'        => $code,
            'success'            => ($code >= 200 && $code < 300),
            'push_server_body'   => $resp_body,
            'push_server_headers'=> $resp_hdrs,
            'auth_header_prefix' => substr($auth_header, 0, 60) . '...',
        ], 200);
    }

    private static function get_origin_static(string $url): string {
        $p = parse_url($url);
        return ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '') . (isset($p['port']) ? ':' . $p['port'] : '');
    }

    private static function detect_browser(string $ua): string {
        if (stripos($ua, 'Firefox') !== false)  return 'Firefox';
        if (stripos($ua, 'Edg') !== false)      return 'Edge';
        if (stripos($ua, 'Chrome') !== false)   return 'Chrome';
        if (stripos($ua, 'Safari') !== false)   return 'Safari';
        return 'Unknown';
    }

    /** DELETE /clear-subscriptions — wipe all stored subscriptions so users re-subscribe fresh */
    public function clear_subscriptions(): WP_REST_Response {
        $deleted = Echorouk_Push_Subscription_DB::truncate_all();
        return new WP_REST_Response([
            'success' => true,
            'deleted' => $deleted,
            'message' => "Deleted $deleted subscriptions. Users will re-subscribe on next page visit.",
        ], 200);
    }
}
