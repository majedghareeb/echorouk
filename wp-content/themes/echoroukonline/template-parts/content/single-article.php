<?php
/**
 * Single article content.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$format       = isset( $args['format'] ) ? sanitize_key( $args['format'] ) : get_post_type();
$ai_summary   = get_post_meta( get_the_ID(), 'ai_summary', true );
$source_name  = get_post_meta( get_the_ID(), 'source_name', true );
$source_url   = get_post_meta( get_the_ID(), 'source_url', true );
$sponsored    = get_post_meta( get_the_ID(), 'sponsored_label', true );
$schema_type  = 'https://schema.org/NewsArticle';
?>
<main id="primary" class="site-main">
	<?php get_template_part( 'template-parts/components/reading-progress' ); ?>
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
		<div class="content-layout single-layout">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?> itemscope itemtype="<?php echo esc_url( $schema_type ); ?>">
				<header class="single-article__header">
					<?php echorouk_the_category_badge(); ?>
					<?php if ( $sponsored ) : ?>
						<span class="sponsored-label"><?php echo esc_html( $sponsored ); ?></span>
					<?php endif; ?>
					<h1 class="single-article__title" itemprop="headline"><?php the_title(); ?></h1>
					<?php echorouk_the_post_meta(); ?>
				</header>

				<?php echorouk_the_ad_slot( 'article_top_ad' ); ?>

				<figure class="single-article__media" itemprop="image">
					<?php echo echorouk_post_image_html( get_the_ID(), 'echorouk-hero', 'single-article__image', true ); ?>
				</figure>

				<?php
				if ( 'video' === $format ) {
					get_template_part( 'template-parts/components/video-embed' );
				} elseif ( 'audio' === $format ) {
					get_template_part( 'template-parts/components/audio-player' );
				} elseif ( 'document' === $format ) {
					get_template_part( 'template-parts/components/pdf-block' );
				} elseif ( 'gallery' === $format ) {
					get_template_part( 'template-parts/components/gallery-block' );
				}
				?>

				<?php if ( echorouk_get_option( 'show_ai_summary', true ) && $ai_summary ) : ?>
					<aside class="ai-summary">
						<h2><?php esc_html_e( 'AI summary', 'echoroukonline' ); ?></h2>
						<div><?php echo wp_kses_post( wpautop( $ai_summary ) ); ?></div>
					</aside>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/components/tts-player' ); ?>

				<div class="entry-content" itemprop="articleBody">
					<?php the_content(); ?>
					<?php wp_link_pages(); ?>
				</div>

				<?php echorouk_the_ad_slot( 'article_middle_ad' ); ?>

				<?php if ( $source_name || $source_url ) : ?>
					<p class="article-source">
						<?php esc_html_e( 'Source:', 'echoroukonline' ); ?>
						<?php if ( $source_url ) : ?>
							<a href="<?php echo esc_url( $source_url ); ?>" rel="nofollow noopener" target="_blank"><?php echo esc_html( $source_name ? $source_name : $source_url ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $source_name ); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<footer class="single-article__footer">
					<?php the_tags( '<div class="tag-list">', '', '</div>' ); ?>
					<?php get_template_part( 'template-parts/components/voting-placeholder' ); ?>
					<?php if ( echorouk_get_option( 'show_social_share', true ) ) : ?>
						<?php get_template_part( 'template-parts/components/social-share' ); ?>
					<?php endif; ?>
				</footer>

				<?php if ( comments_open() || get_comments_number() ) : ?>
					<?php comments_template(); ?>
				<?php endif; ?>
			</article>

			<?php get_sidebar(); ?>
		</div>

		<?php if ( echorouk_get_option( 'show_author_box', true ) ) : ?>
			<?php get_template_part( 'template-parts/components/author-box' ); ?>
		<?php endif; ?>

		<?php if ( echorouk_get_option( 'show_related_articles', true ) ) : ?>
			<?php get_template_part( 'template-parts/components/related-articles' ); ?>
		<?php endif; ?>
	</div>
</main>
