<?php
/**
 * Post editor meta box — "Send Push Notification" panel.
 */

if (! defined('ABSPATH')) exit;

class Echorouk_Push_Post_Meta {

    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post',      [$this, 'save_meta'], 10, 2);
        add_action('publish_post',   [$this, 'on_publish'], 10, 2);
    }

    public function add_meta_box(): void {
        add_meta_box(
            'echorouk-push-meta-box',
            __('إشعار Push', 'echorouk-push'),
            [$this, 'render_meta_box'],
            ['post', 'page'],
            'side',
            'high'
        );
    }

    public function render_meta_box(\WP_Post $post): void {
        wp_nonce_field('echorouk_push_meta_nonce', 'echorouk_push_nonce');

        $auto_send = get_post_meta($post->ID, '_echorouk_push_auto_send', true);
        $sent      = get_post_meta($post->ID, '_echorouk_push_sent', true);
        $count     = Echorouk_Push_Subscription_DB::count_active();
        $icon      = get_the_post_thumbnail_url($post->ID, 'thumbnail') ?: '';

        include ECHOROUK_PUSH_PATH . 'admin/views/meta-box.php';
    }

    public function save_meta(int $post_id, \WP_Post $post): void {
        if (! isset($_POST['echorouk_push_nonce'])) return;
        if (! wp_verify_nonce($_POST['echorouk_push_nonce'], 'echorouk_push_meta_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (! current_user_can('edit_post', $post_id)) return;

        $auto_send = isset($_POST['echorouk_push_auto_send']) ? 1 : 0;
        update_post_meta($post_id, '_echorouk_push_auto_send', $auto_send);
    }

    public function on_publish(int $post_id, \WP_Post $post): void {
        // Only fire once per post
        if (get_post_meta($post_id, '_echorouk_push_sent', true)) return;

        $auto = get_post_meta($post_id, '_echorouk_push_auto_send', true);
        if (! $auto) return;

        $notification = [
            'title' => html_entity_decode(get_the_title($post_id), ENT_QUOTES, 'UTF-8'),
            'body'  => wp_trim_words(strip_tags(get_post_field('post_excerpt', $post_id) ?: get_post_field('post_content', $post_id)), 20, '...'),
            'url'   => get_permalink($post_id),
            'icon'  => get_the_post_thumbnail_url($post_id, 'thumbnail') ?: '',
            'badge' => get_option('echorouk_push_badge_url', ''),
            'image' => get_the_post_thumbnail_url($post_id, 'medium') ?: '',
        ];

        Echorouk_Push_Scheduler::schedule_broadcast($notification);
        update_post_meta($post_id, '_echorouk_push_sent', current_time('mysql', true));
    }
}
