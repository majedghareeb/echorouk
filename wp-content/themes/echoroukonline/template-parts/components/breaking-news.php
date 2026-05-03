<?php
/**
 * Breaking news bar.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$posts = echorouk_get_cached_posts(
	'breaking_news_bar',
	array(
		'post_type'      => echorouk_news_post_types(),
		'posts_per_page' => 1,
		'meta_key'       => 'breaking_news',
		'meta_value'     => 1,
	),
	120
);

if ( empty( $posts ) && ( is_front_page() || is_home() ) && function_exists( 'echorouk_homepage_section_posts' ) ) {
	$posts = echorouk_homepage_section_posts(
		'news_ticker',
		1,
		array(
			'post_type'      => echorouk_news_post_types(),
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

if ( empty( $posts ) ) {
	return;
}

$breaking_post = $posts[0];
?>
<div class="breaking-bar" role="region" aria-label="<?php esc_attr_e( 'Breaking news', 'echoroukonline' ); ?>">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?> breaking-bar__inner">
		<strong class="breaking-bar__label"><?php esc_html_e( 'عاجل', 'echoroukonline' ); ?></strong>
		<a class="breaking-bar__headline" href="<?php echo esc_url( get_permalink( $breaking_post ) ); ?>">
			<?php echo esc_html( get_the_title( $breaking_post ) ); ?>
		</a>
	</div>
</div>
