<?php
/**
 * Homepage hero lead story.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$hero      = null;
$secondary = array();
$section   = echorouk_homepage_section( 'hero' );
$meta      = isset( $section['meta'] ) && is_array( $section['meta'] ) ? $section['meta'] : array();

if ( ! empty( $section['enabled'] ) ) {
	$main_id = ! empty( $meta['main_post_id'] ) ? absint( $meta['main_post_id'] ) : 0;
	$hero    = $main_id ? get_post( $main_id ) : null;

	if ( ! $hero || 'publish' !== $hero->post_status ) {
		$hero = null;
	}

	$live_enabled = ! empty( $meta['live_coverage_enabled'] );
	$live_id      = ! empty( $meta['live_post_id'] ) ? absint( $meta['live_post_id'] ) : 0;
	$live_post    = ( $live_enabled && $live_id ) ? get_post( $live_id ) : null;
	$side_ids     = ! empty( $meta['side_post_ids'] ) && is_array( $meta['side_post_ids'] ) ? array_map( 'absint', $meta['side_post_ids'] ) : array();
	$fallback_ids = ! empty( $meta['fallback_post_ids'] ) && is_array( $meta['fallback_post_ids'] ) ? array_map( 'absint', $meta['fallback_post_ids'] ) : array();
	$right_ids    = ( $live_post && 'publish' === $live_post->post_status ) ? $side_ids : $fallback_ids;

	if ( ! empty( $right_ids ) ) {
		$secondary = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'post__in'       => $right_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 4,
			)
		);
	}
}

if ( ! $hero ) {
	$posts = echorouk_homepage_section_posts(
		'hero',
		5,
		array(
			'post_type'      => echorouk_news_post_types(),
			'posts_per_page' => 5,
			'meta_key'       => 'editorial_pick',
			'meta_value'     => 1,
			'orderby'        => 'date',
		)
	);
	$hero = ! empty( $posts ) ? $posts[0] : null;

	if ( empty( $secondary ) && count( $posts ) > 1 ) {
		$secondary = array_slice( $posts, 1, 4 );
	}
}

if ( ! $hero ) {
	return;
}

if ( empty( $secondary ) ) {
	$secondary = echorouk_get_cached_posts(
		'hero_secondary',
		array(
			'post_type'      => echorouk_news_post_types(),
			'post__not_in'   => array( $hero->ID ),
			'posts_per_page' => 4,
		),
		180
	);
}
?>
<section class="hero-lead">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<div class="hero-lead__grid">
			<article class="hero-lead__main">
				<a class="hero-lead__image-link" href="<?php echo esc_url( get_permalink( $hero ) ); ?>">
					<?php echo echorouk_post_image_html( $hero->ID, 'echorouk-hero', 'hero-lead__image', true ); ?>
				</a>
				<div class="hero-lead__content">
					<?php echorouk_the_category_badge( $hero->ID ); ?>
					<h2><a href="<?php echo esc_url( get_permalink( $hero ) ); ?>"><?php echo esc_html( get_the_title( $hero ) ); ?></a></h2>
					<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $hero ), 28 ) ); ?></p>
				</div>
			</article>

			<div class="hero-lead__side">
				<?php foreach ( $secondary as $post ) : ?>
					<?php echorouk_news_card( $post->ID, 'compact' ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
