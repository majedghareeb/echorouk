<?php
/**
 * Template Name: Section Landing Page
 *
 * @package EchouroukOnline
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<header class="archive-header">
				<h1 class="archive-title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<div class="archive-description"><?php the_excerpt(); ?></div>
				<?php endif; ?>
			</header>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		<?php endwhile; ?>
		<?php get_template_part( 'template-parts/components/category-block' ); ?>
		<?php get_template_part( 'template-parts/components/latest-news' ); ?>
	</div>
</main>
<?php
get_footer();

