<?php if (! defined('ABSPATH')) exit; ?>
<div class="ep-wrap ep-dashboard">

    <div class="ep-header">
        <div class="ep-header__icon">🔔</div>
        <div>
            <h1 class="ep-header__title"><?php esc_html_e('إشعارات Push', 'echorouk-push'); ?></h1>
            <p class="ep-header__sub"><?php esc_html_e('لوحة تحكم المشتركين والإشعارات', 'echorouk-push'); ?></p>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="ep-cards">
        <div class="ep-card ep-card--primary">
            <div class="ep-card__label"><?php esc_html_e('إجمالي المشتركين', 'echorouk-push'); ?></div>
            <div class="ep-card__value"><?php echo number_format_i18n($count); ?></div>
        </div>

        <div class="ep-card ep-card--<?php echo $sw_ok ? 'success' : 'danger'; ?>">
            <div class="ep-card__label"><?php esc_html_e('Service Worker', 'echorouk-push'); ?></div>
            <div class="ep-card__value ep-card__value--sm">
                <?php echo $sw_ok
                    ? '<span class="ep-badge ep-badge--green">✔ ' . esc_html__('نشط', 'echorouk-push') . '</span>'
                    : '<span class="ep-badge ep-badge--red">✘ ' . esc_html__('مفقود', 'echorouk-push') . '</span>';
                ?>
            </div>
            <?php if (! $sw_ok): ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:8px">
                    <input type="hidden" name="action" value="echorouk_push_deploy_sw">
                    <?php wp_nonce_field('echorouk_push_deploy_sw'); ?>
                    <button type="submit" class="ep-btn ep-btn--sm"><?php esc_html_e('نشر الملف', 'echorouk-push'); ?></button>
                </form>
            <?php endif; ?>
        </div>

        <div class="ep-card ep-card--<?php echo $vapid_ok ? 'success' : 'danger'; ?>">
            <div class="ep-card__label"><?php esc_html_e('مفاتيح VAPID', 'echorouk-push'); ?></div>
            <div class="ep-card__value ep-card__value--sm">
                <?php echo $vapid_ok
                    ? '<span class="ep-badge ep-badge--green">✔ ' . esc_html__('مولّدة', 'echorouk-push') . '</span>'
                    : '<span class="ep-badge ep-badge--red">✘ ' . esc_html__('مفقودة', 'echorouk-push') . '</span>';
                ?>
            </div>
        </div>

        <div class="ep-card">
            <div class="ep-card__label"><?php esc_html_e('إشعارات مرسلة', 'echorouk-push'); ?></div>
            <div class="ep-card__value"><?php echo count($log); ?></div>
        </div>
    </div>

    <div class="ep-two-col">
        <!-- Browser Breakdown -->
        <div class="ep-section">
            <h2 class="ep-section__title"><?php esc_html_e('توزيع المتصفحات', 'echorouk-push'); ?></h2>
            <?php if ($by_browser): ?>
                <table class="ep-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('المتصفح', 'echorouk-push'); ?></th>
                            <th><?php esc_html_e('المشتركون', 'echorouk-push'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($by_browser as $row): ?>
                            <tr>
                                <td><?php echo esc_html($row->browser ?: 'Unknown'); ?></td>
                                <td><?php echo number_format_i18n((int) $row->total); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="ep-empty"><?php esc_html_e('لا يوجد مشتركون بعد.', 'echorouk-push'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Recent Sends Log -->
        <div class="ep-section">
            <h2 class="ep-section__title"><?php esc_html_e('آخر الإشعارات المرسلة', 'echorouk-push'); ?></h2>
            <?php if ($log): ?>
                <div class="ep-log">
                    <?php foreach (array_slice($log, 0, 10) as $entry): ?>
                        <div class="ep-log__item">
                            <div class="ep-log__title"><?php echo esc_html($entry['title']); ?></div>
                            <div class="ep-log__meta">
                                <?php printf(
                                    esc_html__('تم الإرسال: %1$s | نجح: %2$d | فشل: %3$d', 'echorouk-push'),
                                    esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($entry['time']))),
                                    (int) ($entry['stats']['sent'] ?? 0),
                                    (int) ($entry['stats']['failed'] ?? 0)
                                ); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="ep-empty"><?php esc_html_e('لم يتم إرسال أي إشعار بعد.', 'echorouk-push'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="ep-actions">
        <a href="<?php echo esc_url(admin_url('admin.php?page=echorouk-push-send')); ?>" class="ep-btn">
            <?php esc_html_e('إرسال إشعار جديد', 'echorouk-push'); ?>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=echorouk-push-settings')); ?>" class="ep-btn ep-btn--secondary">
            <?php esc_html_e('الإعدادات', 'echorouk-push'); ?>
        </a>
    </div>
</div>
