<?php
/**
 * Search results.
 *
 * @package EchouroukOnline
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
		<header class="archive-header">
			<h1 class="archive-title">
				<?php
				printf(
					esc_html__( 'Search results for: %s', 'echoroukonline' ),
					'<span>' . esc_html( get_search_query() ) . '</span>'
				);
				?>
			</h1>
		</header>

		<div class="content-layout">
			<section class="content-main">
				<?php if ( have_posts() ) : ?>
					<div class="archive-grid">
						<?php
						while ( have_posts() ) :
							the_post();
							echorouk_news_card( get_the_ID() );
						endwhile;
						?>
					</div>
					<?php echorouk_the_posts_pagination(); ?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content/none' ); ?>
				<?php endif; ?>
			</section>
			<?php get_sidebar(); ?>
		</div>
	</div>
</main>
<?php
get_footer();

