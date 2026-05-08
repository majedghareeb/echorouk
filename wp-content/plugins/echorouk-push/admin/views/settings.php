<?php if (! defined('ABSPATH')) exit; ?>
<div class="ep-wrap">

    <div class="ep-header">
        <div class="ep-header__icon">⚙️</div>
        <div>
            <h1 class="ep-header__title"><?php esc_html_e('إعدادات Push', 'echorouk-push'); ?></h1>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('تم حفظ الإعدادات.', 'echorouk-push'); ?></p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ep-settings-form">
        <input type="hidden" name="action" value="echorouk_push_save_settings">
        <?php wp_nonce_field('echorouk_push_settings_nonce'); ?>

        <div class="ep-section">
            <h2><?php esc_html_e('الأيقونات', 'echorouk-push'); ?></h2>

            <div class="ep-form-group">
                <label for="icon_url"><?php esc_html_e('أيقونة الإشعار (192×192 px)', 'echorouk-push'); ?></label>
                <input type="url" name="icon_url" id="icon_url" class="ep-input"
                       value="<?php echo esc_url($icon_url); ?>"
                       placeholder="https://echorouk.net/wp-content/uploads/icon-192.png">
                <p class="ep-help"><?php esc_html_e('تظهر بجانب كل إشعار. يُنصح بحجم 192×192 بكسل.', 'echorouk-push'); ?></p>
            </div>

            <div class="ep-form-group">
                <label for="badge_url"><?php esc_html_e('شارة الإشعار (72×72 px)', 'echorouk-push'); ?></label>
                <input type="url" name="badge_url" id="badge_url" class="ep-input"
                       value="<?php echo esc_url($badge_url); ?>"
                       placeholder="https://echorouk.net/wp-content/uploads/badge-72.png">
                <p class="ep-help"><?php esc_html_e('تظهر في شريط الحالة على Android. يُنصح بحجم 72×72 بكسل.', 'echorouk-push'); ?></p>
            </div>
        </div>

        <div class="ep-section">
            <h2><?php esc_html_e('مفاتيح VAPID', 'echorouk-push'); ?></h2>
            <div class="ep-vapid-box">
                <div class="ep-form-group">
                    <label><?php esc_html_e('المفتاح العام (Public Key)', 'echorouk-push'); ?></label>
                    <input type="text" class="ep-input ep-input--mono" readonly
                           value="<?php echo esc_attr($public_key); ?>">
                    <p class="ep-help ep-help--warning">
                        ⚠️ <?php esc_html_e('لا تقم بتجديد هذه المفاتيح بعد وجود مشتركين — سيؤدي ذلك إلى فقدانهم جميعاً.', 'echorouk-push'); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="ep-section">
            <h2><?php esc_html_e('Service Worker', 'echorouk-push'); ?></h2>
            <?php $sw_path = ABSPATH . 'echorouk-push-sw.js'; ?>
            <p>
                <?php if (file_exists($sw_path)): ?>
                    <span class="ep-badge ep-badge--green">✔ <?php esc_html_e('الملف موجود', 'echorouk-push'); ?></span>
                    — <code><?php echo esc_html($sw_path); ?></code>
                <?php else: ?>
                    <span class="ep-badge ep-badge--red">✘ <?php esc_html_e('الملف مفقود', 'echorouk-push'); ?></span>
                <?php endif; ?>
            </p>
        </div>

        <button type="submit" class="ep-btn"><?php esc_html_e('حفظ الإعدادات', 'echorouk-push'); ?></button>
    </form>
</div>
