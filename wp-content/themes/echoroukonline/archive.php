<?php
/**
 * Archive and section landing template.
 *
 * @package EchouroukOnline
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
		<header class="archive-header">
			<h1 class="archive-title"><?php echorouk_archive_title(); ?></h1>
			<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
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

