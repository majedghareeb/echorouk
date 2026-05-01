<?php
/**
 * WordPress author archive.
 *
 * @package EchouroukOnline
 */

get_header();
$author_id = get_queried_object_id();
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
		<header class="author-profile">
			<?php echo get_avatar( $author_id, 120, '', '', array( 'class' => 'author-profile__avatar' ) ); ?>
			<div>
				<h1><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></h1>
				<?php if ( get_the_author_meta( 'description', $author_id ) ) : ?>
					<div class="author-profile__bio"><?php echo wp_kses_post( wpautop( get_the_author_meta( 'description', $author_id ) ) ); ?></div>
				<?php endif; ?>
			</div>
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

