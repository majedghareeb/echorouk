<?php
/**
 * Single live coverage content template.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$post_id      = get_the_ID();
$author       = echorouk_get_post_author_data( $post_id );
$has_rail     = 'none' !== echorouk_get_option( 'sidebar_position', 'right' );
$show_comments = ! echorouk_get_option( 'disable_comment_box', true );
$deck_text    = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ), 25, '...' );
$updates_key  = function_exists( 'echorouk_live_coverage_meta_key' ) ? echorouk_live_coverage_meta_key() : 'live_coverage_updates';
$updates_raw  = get_post_meta( $post_id, $updates_key, true );
$updates      = is_array( $updates_raw ) ? array_values( $updates_raw ) : array();

if ( ! empty( $updates ) ) {
	usort(
		$updates,
		static function ( $a, $b ) {
			$time_a = ! empty( $a['time'] ) ? strtotime( (string) $a['time'] ) : 0;
			$time_b = ! empty( $b['time'] ) ? strtotime( (string) $b['time'] ) : 0;
			return $time_b <=> $time_a;
		}
	);
}
?>
<main id="primary" class="site-main single-article-main single-live-coverage-main">
	<?php get_template_part( 'template-parts/components/reading-progress' ); ?>
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article single-article--feature single-live-coverage' ); ?>>
			<div class="single-article__lead">
				<figure class="single-article__media">
					<?php echo echorouk_post_image_html( $post_id, 'echorouk-hero', 'single-article__image', true ); ?>
				</figure>
				<header class="single-article__header">
					<h1 class="single-article__title"><?php the_title(); ?></h1>
					<?php if ( $deck_text ) : ?>
						<p class="single-article__deck"><?php echo esc_html( $deck_text ); ?></p>
					<?php endif; ?>
					<div class="single-article__author-line">
						<a class="single-article__author-avatar" href="<?php echo esc_url( $author['url'] ); ?>" aria-label="<?php echo esc_attr( $author['name'] ); ?>">
							<?php echo $author['avatar']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
						<div class="single-article__author-copy">
							<a class="single-article__author-name" href="<?php echo esc_url( $author['url'] ); ?>"><?php echo esc_html( $author['name'] ); ?></a>
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time>
						</div>
					</div>
				</header>
			</div>

			<div class="single-article__divider"></div>

			<div class="single-article__body-layout<?php echo $has_rail ? '' : ' single-article__body-layout--no-rail'; ?>">
				<?php if ( $has_rail ) : ?>
					<?php get_sidebar(); ?>
				<?php endif; ?>

				<div class="single-article__body">
					<section class="live-coverage-timeline" aria-label="<?php esc_attr_e( 'Live Coverage', 'echoroukonline' ); ?>">
						<header class="live-coverage-timeline__header">
							<h2><?php esc_html_e( 'Live Coverage', 'echoroukonline' ); ?></h2>
						</header>
						<?php if ( ! empty( $updates ) ) : ?>
							<ul class="live-coverage-timeline__list">
								<?php foreach ( $updates as $item ) : ?>
									<?php
									$raw_time = isset( $item['time'] ) ? (string) $item['time'] : '';
									$ts       = $raw_time ? strtotime( $raw_time ) : false;
									$display  = $ts ? wp_date( 'H:i', $ts ) : $raw_time;
									$datetime = $ts ? gmdate( DATE_W3C, $ts ) : '';
									$text     = isset( $item['text'] ) ? sanitize_text_field( (string) $item['text'] ) : '';
									$url      = isset( $item['url'] ) ? esc_url( (string) $item['url'] ) : '';
									if ( '' === $text ) {
										continue;
									}
									?>
									<li class="live-coverage-timeline__item">
										<?php if ( $display ) : ?>
											<time class="live-coverage-timeline__time" datetime="<?php echo esc_attr( $datetime ); ?>"><?php echo esc_html( $display ); ?></time>
										<?php endif; ?>
										<div class="live-coverage-timeline__text">
											<?php if ( $url ) : ?>
												<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $text ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $text ); ?>
											<?php endif; ?>
										</div>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php else : ?>
							<p class="live-coverage-timeline__empty"><?php esc_html_e( 'No live updates yet.', 'echoroukonline' ); ?></p>
						<?php endif; ?>
					</section>

					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages(); ?>
					</div>

					<?php if ( $show_comments && ( comments_open() || get_comments_number() ) ) : ?>
						<?php comments_template(); ?>
					<?php endif; ?>
				</div>
			</div>
		</article>
	</div>
</main>
