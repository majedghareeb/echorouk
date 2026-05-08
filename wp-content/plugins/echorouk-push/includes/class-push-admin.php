<?php
/**
 * Admin menu, pages, and enqueue for Echorouk Push.
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_Admin {

    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void {
        add_action('admin_menu',            [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_post_echorouk_push_save_settings', [$this, 'save_settings']);
        add_action('admin_post_echorouk_push_deploy_sw',      [$this, 'deploy_sw']);
    }

    public function register_menu(): void {
        add_menu_page(
            __('Push Notifications', 'echorouk-push'),
            __('Push Notifications', 'echorouk-push'),
            'edit_posts',
            'echorouk-push',
            [$this, 'render_dashboard'],
            'dashicons-bell',
            25
        );

        add_submenu_page(
            'echorouk-push',
            __('لوحة التحكم', 'echorouk-push'),
            __('لوحة التحكم', 'echorouk-push'),
            'edit_posts',
            'echorouk-push',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'echorouk-push',
            __('إرسال إشعار', 'echorouk-push'),
            __('إرسال إشعار', 'echorouk-push'),
            'edit_posts',
            'echorouk-push-send',
            [$this, 'render_campaigns']
        );

        add_submenu_page(
            'echorouk-push',
            __('الإعدادات', 'echorouk-push'),
            __('الإعدادات', 'echorouk-push'),
            'manage_options',
            'echorouk-push-settings',
            [$this, 'render_settings']
        );
    }

    public function enqueue_assets(string $hook): void {
        if (strpos($hook, 'echorouk-push') === false) return;

        wp_enqueue_style(
            'echorouk-push-admin',
            ECHOROUK_PUSH_URL . 'assets/css/admin.css',
            [],
            ECHOROUK_PUSH_VERSION
        );

        wp_enqueue_script(
            'echorouk-push-admin',
            ECHOROUK_PUSH_URL . 'assets/js/echorouk-push-admin.js',
            ['wp-api-fetch'],
            ECHOROUK_PUSH_VERSION,
            true
        );

        wp_localize_script('echorouk-push-admin', 'EchoroukPushAdmin', [
            'restUrl'    => rest_url('echorouk-push/v1'),
            'nonce'      => wp_create_nonce('wp_rest'),
            'wpApiRoot'  => rest_url(),
            'postTypes'  => get_post_types(['public' => true], 'objects'),
        ]);

        // Also expose wpApiSettings for WP core JS compatibility
        wp_add_inline_script(
            'echorouk-push-admin',
            'window.wpApiSettings = window.wpApiSettings || { root: ' . json_encode(rest_url()) . ', nonce: ' . json_encode(wp_create_nonce('wp_rest')) . ' };',
            'before'
        );
    }

    public function render_dashboard(): void {
        $count         = Echorouk_Push_Subscription_DB::count_active();
        $by_browser    = Echorouk_Push_Subscription_DB::count_by_browser();
        $log           = get_option('echorouk_push_send_log', []);
        $sw_ok         = file_exists(ABSPATH . 'echorouk-push-sw.js');
        $vapid_ok      = (bool) Echorouk_Push_VAPID::get_public_key();
        include ECHOROUK_PUSH_PATH . 'admin/views/dashboard.php';
    }

    public function render_campaigns(): void {
        include ECHOROUK_PUSH_PATH . 'admin/views/campaigns.php';
    }

    public function render_settings(): void {
        $badge_url   = get_option('echorouk_push_badge_url', '');
        $icon_url    = get_option('echorouk_push_icon_url', '');
        $public_key  = Echorouk_Push_VAPID::get_public_key();
        include ECHOROUK_PUSH_PATH . 'admin/views/settings.php';
    }

    public function save_settings(): void {
        if (! current_user_can('manage_options')) wp_die('Forbidden', 403);
        check_admin_referer('echorouk_push_settings_nonce');

        update_option('echorouk_push_badge_url', esc_url_raw($_POST['badge_url'] ?? ''));
        update_option('echorouk_push_icon_url',  esc_url_raw($_POST['icon_url']  ?? ''));

        wp_redirect(admin_url('admin.php?page=echorouk-push-settings&updated=1'));
        exit;
    }

    public function deploy_sw(): void {
        if (! current_user_can('manage_options')) wp_die('Forbidden', 403);
        check_admin_referer('echorouk_push_deploy_sw');
        Echorouk_Push::deploy_service_worker();
        wp_redirect(admin_url('admin.php?page=echorouk-push'));
        exit;
    }
}
