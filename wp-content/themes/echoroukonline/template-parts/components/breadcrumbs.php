<?php

/**
 * Breadcrumbs.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;

if (is_front_page() || ! echorouk_get_option('enable_breadcrumbs', true)) {
	return;
}

$breadcrumb_items = function_exists('echorouk_get_breadcrumb_items') ? echorouk_get_breadcrumb_items() : array();
if (empty($breadcrumb_items)) {
	return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'echoroukonline'); ?>">
	<ol>
		<?php foreach ($breadcrumb_items as $index => $item) : ?>
			<?php
			$name       = isset($item['name']) ? (string) $item['name'] : '';
			$url        = isset($item['url']) ? (string) $item['url'] : '';
			$is_current = $index === (count($breadcrumb_items) - 1);
			if ('' === trim($name)) {
				continue;
			}
			?>
			<li<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
				<?php if (! $is_current && $url) : ?>
					<a href="<?php echo esc_url($url); ?>"><?php echo esc_html($name); ?></a>
				<?php else : ?>
					<?php echo esc_html($name); ?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
