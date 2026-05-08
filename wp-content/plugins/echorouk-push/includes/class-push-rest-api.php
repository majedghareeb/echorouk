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

    private static function detect_browser(string $ua): string {
        if (stripos($ua, 'Firefox') !== false)  return 'Firefox';
        if (stripos($ua, 'Edg') !== false)      return 'Edge';
        if (stripos($ua, 'Chrome') !== false)   return 'Chrome';
        if (stripos($ua, 'Safari') !== false)   return 'Safari';
        return 'Unknown';
    }
}
