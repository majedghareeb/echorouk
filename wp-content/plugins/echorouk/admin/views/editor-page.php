<?php
if (! defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap echorouk-homepage-editor-wrap">
    <h1><?php esc_html_e('Homepage Editorial Panel', 'echorouk-homepage'); ?></h1>
    <p class="description">
        <?php esc_html_e('Reorder homepage sections with drag-and-drop, choose post sources, and control section visibility.', 'echorouk-homepage'); ?>
    </p>

    <div id="echorouk-homepage-editor-app" class="echorouk-homepage-editor-app">
        <p class="echorouk-loading"><?php esc_html_e('Loading configuration...', 'echorouk-homepage'); ?></p>
    </div>
</div>
