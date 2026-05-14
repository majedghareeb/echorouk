<?php
/**
 * Home page.
 *
 * @package EchouroukOnline
 */

get_header();
?>
<?php
$front_page_id   = (int) get_option( 'page_on_front' );
$front_template  = $front_page_id ? get_page_template_slug( $front_page_id ) : '';
$is_mockup_front = 'page-templates/homepage-mockup.php' === $front_template;
$home_h1         = $front_page_id ? get_the_title( $front_page_id ) : '';

if ( '' === trim( (string) $home_h1 ) ) {
	$home_h1 = get_bloginfo( 'name' );
}

if ( $is_mockup_front ) :
	printf(
		'<h1 class="screen-reader-text">%s</h1>',
		esc_html( $home_h1 )
	);
	get_template_part( 'template-parts/pages/homepage-mockup' );
else :
	?>
	<main id="primary" class="site-main">
		<h1 class="screen-reader-text"><?php echo esc_html( $home_h1 ); ?></h1>
		<?php get_template_part( 'template-parts/components/hero-lead' ); ?>

		<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
			<?php if ( echorouk_get_option( 'featured_section_enabled', true ) ) : ?>
				<?php get_template_part( 'template-parts/components/category-block' ); ?>
			<?php endif; ?>

			<div class="home-layout">
				<div class="home-layout__main">
					<?php if ( echorouk_get_option( 'latest_news_enabled', true ) ) : ?>
						<?php get_template_part( 'template-parts/components/latest-news' ); ?>
					<?php endif; ?>

					<?php if ( echorouk_get_option( 'video_section_enabled', true ) ) : ?>
						<?php get_template_part( 'template-parts/components/video-list' ); ?>
					<?php endif; ?>
				</div>

				<aside class="home-layout__side" aria-label="<?php esc_attr_e( 'Homepage sidebar', 'echoroukonline' ); ?>">
					<?php if ( echorouk_get_option( 'most_read_enabled', true ) ) : ?>
						<?php get_template_part( 'template-parts/components/most-read' ); ?>
					<?php endif; ?>

					<?php if ( echorouk_get_option( 'editorial_section_enabled', true ) ) : ?>
						<?php get_template_part( 'template-parts/components/editorial-recommendations' ); ?>
					<?php endif; ?>

					<?php if ( echorouk_get_option( 'newsletter_enabled', true ) ) : ?>
						<?php get_template_part( 'template-parts/components/newsletter' ); ?>
					<?php endif; ?>
				</aside>
			</div>
		</div>
	</main>
	<?php
endif;
?>
<?php
get_footer();
