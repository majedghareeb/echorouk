<?php
/**
 * Guest authors archive template.
 *
 * @package EchouroukOnline
 */

get_header();
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
		<header class="archive-header">
			<h1 class="archive-title"><?php post_type_archive_title(); ?></h1>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="guest-author-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article class="guest-author-card">
						<a href="<?php the_permalink(); ?>"><?php echo echorouk_post_image_html( get_the_ID(), 'thumbnail', 'guest-author-card__avatar' ); ?></a>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php if ( get_post_meta( get_the_ID(), 'guest_job_title', true ) ) : ?>
							<p><?php echo esc_html( get_post_meta( get_the_ID(), 'guest_job_title', true ) ); ?></p>
						<?php endif; ?>
					</article>
				<?php endwhile; ?>
			</div>
			<?php echorouk_the_posts_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content/none' ); ?>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
