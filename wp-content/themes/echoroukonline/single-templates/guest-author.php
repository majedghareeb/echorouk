<?php
/**
 * Guest author single template.
 *
 * @package EchouroukOnline
 */

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<main id="primary" class="site-main">
		<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
			<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
			<header class="author-profile">
				<?php echo echorouk_post_image_html( get_the_ID(), 'thumbnail', 'author-profile__avatar', true ); ?>
				<div>
					<h1><?php the_title(); ?></h1>
					<?php if ( get_post_meta( get_the_ID(), 'guest_job_title', true ) ) : ?>
						<p class="author-profile__role"><?php echo esc_html( get_post_meta( get_the_ID(), 'guest_job_title', true ) ); ?></p>
					<?php endif; ?>
					<div class="author-profile__bio"><?php the_content(); ?></div>
				</div>
			</header>

			<?php
			$posts = new WP_Query(
				array(
					'post_type'      => echorouk_article_post_types(),
					'posts_per_page' => 12,
					'meta_key'       => 'guest_author_id',
					'meta_value'     => get_the_ID(),
				)
			);
			if ( $posts->have_posts() ) :
				?>
				<section class="section-block">
					<h2 class="section-title"><?php esc_html_e( 'Latest articles', 'echoroukonline' ); ?></h2>
					<div class="archive-grid">
						<?php
						while ( $posts->have_posts() ) :
							$posts->the_post();
							echorouk_news_card( get_the_ID() );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;
get_footer();
