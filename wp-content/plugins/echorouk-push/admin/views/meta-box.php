<?php if (! defined('ABSPATH')) exit;
/** @var WP_Post $post */
/** @var string $auto_send */
/** @var string $sent */
/** @var int $count */
/** @var string $icon */
?>
<div class="ep-meta-box">
    <?php if ($sent): ?>
        <div class="ep-meta-sent">
            ✅ <?php printf(
                esc_html__('تم الإرسال: %s', 'echorouk-push'),
                esc_html(wp_date(get_option('date_format') . ' H:i', strtotime($sent)))
            ); ?>
        </div>
    <?php endif; ?>

    <label class="ep-meta-check">
        <input type="checkbox" name="echorouk_push_auto_send" value="1"
               <?php checked((bool) $auto_send); ?>>
        <?php esc_html_e('إرسال إشعار عند النشر', 'echorouk-push'); ?>
    </label>

    <p class="ep-meta-help">
        <?php printf(
            esc_html__('سيتم الإرسال إلى %s مشترك.', 'echorouk-push'),
            '<strong>' . number_format_i18n($count) . '</strong>'
        ); ?>
    </p>
</div>
