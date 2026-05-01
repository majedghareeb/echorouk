<?php
/**
 * News card.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : get_the_ID();
$variant = isset( $args['variant'] ) ? sanitize_key( $args['variant'] ) : 'default';
$is_lcp  = ! empty( $args['is_lcp'] );
$post    = get_post( $post_id );

if ( ! $post ) {
	return;
}

$classes = 'news-card news-card--' . $variant;
?>
<article class="<?php echo esc_attr( $classes ); ?>">
	<a class="news-card__image-link" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<?php echo echorouk_post_image_html( $post_id, 'compact' === $variant ? 'echorouk-card' : 'medium_large', 'news-card__image', $is_lcp ); ?>
	</a>
	<div class="news-card__body">
		<?php echorouk_the_category_badge( $post_id ); ?>
		<h2 class="news-card__title"><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h2>
		<?php if ( 'compact' !== $variant ) : ?>
			<p class="news-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 18 ) ); ?></p>
		<?php endif; ?>
		<div class="news-card__meta">
			<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time>
		</div>
	</div>
</article>

