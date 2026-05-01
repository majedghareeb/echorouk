<?php
/**
 * 404 template.
 *
 * @package EchouroukOnline
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<section class="not-found">
			<h1><?php esc_html_e( 'Page not found', 'echoroukonline' ); ?></h1>
			<p><?php esc_html_e( 'The requested page could not be found. Try searching or browse the latest stories.', 'echoroukonline' ); ?></p>
			<?php get_search_form(); ?>
		</section>
		<?php get_template_part( 'template-parts/components/latest-news' ); ?>
	</div>
</main>
<?php
get_footer();

