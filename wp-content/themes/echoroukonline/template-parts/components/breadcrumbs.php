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
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'echoroukonline'); ?>">
	<ol>
		<li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'echoroukonline'); ?></a>
		</li>
		<?php if (is_singular()) : ?>
			<?php $category = echorouk_get_primary_category(); ?>
			<?php if ($category) : ?>
				<li><a
						href="<?php echo esc_url(get_category_link($category)); ?>"><?php echo esc_html($category->name); ?></a>
				</li>
			<?php elseif ('post' !== get_post_type()) : ?>
				<li><a
						href="<?php echo esc_url(get_post_type_archive_link(get_post_type())); ?>"><?php echo esc_html(get_post_type_object(get_post_type())->labels->name); ?></a>
				</li>
			<?php endif; ?>
			<li aria-current="page"><?php the_title(); ?></li>
		<?php elseif (is_category() || is_tag() || is_tax()) : ?>
			<li aria-current="page"><?php single_term_title(); ?></li>
		<?php elseif (is_search()) : ?>
			<li aria-current="page"><?php esc_html_e('Search', 'echoroukonline'); ?></li>
		<?php elseif (is_post_type_archive()) : ?>
			<li aria-current="page"><?php post_type_archive_title(); ?></li>
		<?php else : ?>
			<li aria-current="page"><?php echorouk_archive_title(); ?></li>
		<?php endif; ?>
	</ol>
</nav>