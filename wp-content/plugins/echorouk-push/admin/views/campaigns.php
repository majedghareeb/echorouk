<?php if (! defined('ABSPATH')) exit; ?>
<div class="ep-wrap">

    <div class="ep-header">
        <div class="ep-header__icon">📢</div>
        <div>
            <h1 class="ep-header__title"><?php esc_html_e('إرسال إشعار Push', 'echorouk-push'); ?></h1>
            <p class="ep-header__sub"><?php printf(
                esc_html__('سيتم الإرسال إلى %s مشترك نشط', 'echorouk-push'),
                '<strong>' . number_format_i18n(Echorouk_Push_Subscription_DB::count_active()) . '</strong>'
            ); ?></p>
        </div>
    </div>

    <div class="ep-campaign-form">
        <div class="ep-form-group">
            <label for="ep-post-search"><?php esc_html_e('اختر مقال (اختياري)', 'echorouk-push'); ?></label>
            <div class="ep-post-search-wrap">
                <input type="text" id="ep-post-search" class="ep-input" placeholder="<?php esc_attr_e('ابحث عن مقال...', 'echorouk-push'); ?>">
                <div id="ep-post-results" class="ep-post-results" hidden></div>
            </div>
        </div>

        <div id="ep-selected-post" class="ep-selected-post" hidden>
            <img id="ep-post-thumbnail" src="" alt="" class="ep-post-thumb"
                 style="display:none" onerror="this.style.display='none'">
            <div>
                <div id="ep-post-title-display" class="ep-post-title-display"></div>
                <button type="button" id="ep-clear-post" class="ep-btn-link"><?php esc_html_e('إلغاء التحديد', 'echorouk-push'); ?></button>
            </div>
        </div>

        <hr class="ep-divider">

        <div class="ep-form-group">
            <label for="ep-title"><?php esc_html_e('عنوان الإشعار', 'echorouk-push'); ?> <span class="ep-required">*</span></label>
            <input type="text" id="ep-title" class="ep-input" maxlength="100" required
                   placeholder="<?php esc_attr_e('مثال: عاجل — آخر الأخبار', 'echorouk-push'); ?>">
            <span class="ep-char-count" data-max="100">0 / 100</span>
        </div>

        <div class="ep-form-group">
            <label for="ep-body"><?php esc_html_e('نص الإشعار', 'echorouk-push'); ?></label>
            <textarea id="ep-body" class="ep-input ep-textarea" rows="3" maxlength="200"
                      placeholder="<?php esc_attr_e('وصف مختصر للخبر...', 'echorouk-push'); ?>"></textarea>
            <span class="ep-char-count" data-max="200">0 / 200</span>
        </div>

        <div class="ep-form-row">
            <div class="ep-form-group">
                <label for="ep-icon"><?php esc_html_e('رابط الأيقونة', 'echorouk-push'); ?></label>
                <input type="url" id="ep-icon" class="ep-input"
                       value="<?php echo esc_url(get_option('echorouk_push_icon_url', '')); ?>"
                       placeholder="https://...">
            </div>
            <div class="ep-form-group">
                <label for="ep-image"><?php esc_html_e('رابط الصورة الكبيرة', 'echorouk-push'); ?></label>
                <input type="url" id="ep-image" class="ep-input" placeholder="https://...">
            </div>
        </div>

        <div class="ep-form-group">
            <label for="ep-url"><?php esc_html_e('رابط الوجهة', 'echorouk-push'); ?></label>
            <input type="url" id="ep-url" class="ep-input"
                   value="<?php echo esc_url(home_url('/')); ?>"
                   placeholder="https://...">
        </div>

        <!-- Preview -->
        <div class="ep-preview-section">
            <h3><?php esc_html_e('معاينة الإشعار', 'echorouk-push'); ?></h3>
            <div class="ep-preview-card">
<?php $icon_url = get_option('echorouk_push_icon_url', ''); ?>
                <img id="ep-preview-icon"
                     src="<?php echo esc_url($icon_url); ?>"
                     alt=""
                     class="ep-preview-icon"
                     style="<?php echo $icon_url ? '' : 'display:none'; ?>"
                     onerror="this.style.display='none'">
                <div class="ep-preview-content">
                    <div id="ep-preview-title" class="ep-preview-title"><?php esc_html_e('عنوان الإشعار', 'echorouk-push'); ?></div>
                    <div id="ep-preview-body" class="ep-preview-body"><?php esc_html_e('نص الإشعار...', 'echorouk-push'); ?></div>
                    <div class="ep-preview-site"><?php echo esc_html(parse_url(home_url(), PHP_URL_HOST)); ?></div>
                </div>
                <img id="ep-preview-image" src="" alt="" class="ep-preview-image" style="display:none">
            </div>
        </div>

        <div class="ep-send-bar">
            <button id="ep-send-btn" class="ep-btn ep-btn--large">
                <span class="ep-btn-label"><?php esc_html_e('إرسال الآن', 'echorouk-push'); ?></span>
                <span class="ep-btn-spinner" hidden>⏳</span>
            </button>
            <div id="ep-send-result" class="ep-send-result" hidden></div>
        </div>
    </div>
</div>
