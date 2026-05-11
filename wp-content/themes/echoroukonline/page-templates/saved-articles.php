<?php

/**
 * Saved Articles route template.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;

get_header();
?>
<main id="primary" class="site-main saved-articles-page" data-saved-articles-page>
	<div class="<?php echo esc_attr(echorouk_container_class()); ?>">
		<?php get_template_part('template-parts/components/breadcrumbs'); ?>

		<header class="archive-header saved-articles-header">
			<h1 class="archive-title"><?php esc_html_e('	', 'echoroukonline'); ?></h1>
			<p class="archive-description">
				<?php esc_html_e('Articles you saved will appear here.', 'echoroukonline'); ?></p>
		</header>

		<section class="saved-articles-list-wrap"
			data-remove-label="<?php echo esc_attr__('Remove', 'echoroukonline'); ?>">
			<ul class="saved-articles-list" data-saved-articles-list></ul>
			<p class="saved-articles-empty" data-saved-articles-empty hidden>
				<?php esc_html_e('No saved articles yet.', 'echoroukonline'); ?>
			</p>
		</section>
	</div>
</main>
<?php
get_footer();
