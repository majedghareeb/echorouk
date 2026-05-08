<?php
/**
 * Plugin Name: Echorouk Push
 * Plugin URI:  https://echorouk.net
 * Description: Web Push Notifications — self-hosted, no third-party service required.
 * Version:     1.0.0
 * Author:      Echorouk
 * Text Domain: echorouk-push
 * Domain Path: /languages
 */

if (! defined('ABSPATH')) {
    exit;
}

define('ECHOROUK_PUSH_VERSION', '1.0.0');
define('ECHOROUK_PUSH_FILE', __FILE__);
define('ECHOROUK_PUSH_PATH', plugin_dir_path(__FILE__));
define('ECHOROUK_PUSH_URL', plugin_dir_url(__FILE__));

require_once ECHOROUK_PUSH_PATH . 'includes/class-push-vapid.php';
require_once ECHOROUK_PUSH_PATH . 'includes/class-push-crypto.php';
require_once ECHOROUK_PUSH_PATH . 'includes/class-push-subscription-db.php';
require_once ECHOROUK_PUSH_PATH . 'includes/class-push-sender.php';
require_once ECHOROUK_PUSH_PATH . 'includes/class-push-rest-api.php';
require_once ECHOROUK_PUSH_PATH . 'includes/class-push-admin.php';
require_once ECHOROUK_PUSH_PATH . 'includes/class-push-post-meta.php';
require_once ECHOROUK_PUSH_PATH . 'includes/class-push-scheduler.php';

register_activation_hook(ECHOROUK_PUSH_FILE, ['Echorouk_Push', 'activate']);
register_deactivation_hook(ECHOROUK_PUSH_FILE, ['Echorouk_Push', 'deactivate']);

class Echorouk_Push {

    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_notices', [$this, 'sw_missing_notice']);
    }

    public function init(): void {
        Echorouk_Push_REST_API::instance()->init();
        Echorouk_Push_Admin::instance()->init();
        Echorouk_Push_Post_Meta::instance()->init();
        Echorouk_Push_Scheduler::instance()->init();

        if (! is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend']);
        }
    }

    public function enqueue_frontend(): void {
        wp_enqueue_style(
            'echorouk-push',
            ECHOROUK_PUSH_URL . 'assets/css/frontend.css',
            [],
            ECHOROUK_PUSH_VERSION
        );

        wp_enqueue_script(
            'echorouk-push',
            ECHOROUK_PUSH_URL . 'assets/js/echorouk-push.js',
            [],
            ECHOROUK_PUSH_VERSION,
            true
        );

        // Resolve theme icon URLs if the theme constant is available
        $theme_uri = defined('ECHOROUK_THEME_URI') ? ECHOROUK_THEME_URI : get_template_directory_uri();

        wp_localize_script('echorouk-push', 'EchoroukPush', [
            'vapidPublicKey' => Echorouk_Push_VAPID::get_public_key(),
            'restUrl'        => rest_url('echorouk-push/v1'),
            'nonce'          => wp_create_nonce('wp_rest'),
            'swUrl'          => home_url('/echorouk-push-sw.js'),
            'iconOff'        => $theme_uri . '/assets/icons/notification-off-01-stroke-rounded.svg',
            'iconOn'         => $theme_uri . '/assets/icons/notification-01-stroke-rounded.svg',
            'i18n'           => [
                'bannerTitle'  => __('اشترك في الإشعارات', 'echorouk-push'),
                'bannerBody'   => __('احصل على آخر الأخبار فور نشرها', 'echorouk-push'),
                'allowButton'  => __('اشتراك', 'echorouk-push'),
                'denyButton'   => __('لاحقاً', 'echorouk-push'),
                'subscribed'   => __('مشترك ✓', 'echorouk-push'),
                'unsubscribe'  => __('إلغاء الاشتراك', 'echorouk-push'),
                'denied'       => __('الإشعارات محظورة', 'echorouk-push'),
            ],
        ]);
    }

    public function sw_missing_notice(): void {
        if (! current_user_can('manage_options')) return;
        $sw = ABSPATH . 'echorouk-push-sw.js';
        if (! file_exists($sw)) {
            echo '<div class="notice notice-error"><p>';
            printf(
                /* translators: %s: file path */
                esc_html__('Echorouk Push: Service Worker file is missing at %s. Please deactivate and reactivate the plugin, or copy it manually.', 'echorouk-push'),
                '<code>' . esc_html(ABSPATH . 'echorouk-push-sw.js') . '</code>'
            );
            echo '</p></div>';
        }
    }

    public static function activate(): void {
        Echorouk_Push_VAPID::generate_keys_if_missing();
        Echorouk_Push_Subscription_DB::create_table();
        self::deploy_service_worker();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        $sw = ABSPATH . 'echorouk-push-sw.js';
        if (file_exists($sw)) {
            @unlink($sw);
        }
        flush_rewrite_rules();
    }

    public static function deploy_service_worker(): bool {
        $source = ECHOROUK_PUSH_PATH . 'echorouk-push-sw.js';
        $dest   = ABSPATH . 'echorouk-push-sw.js';
        if (file_exists($source) && is_writable(ABSPATH)) {
            return copy($source, $dest);
        }
        return false;
    }
}

Echorouk_Push::instance();
