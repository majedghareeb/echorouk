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
		'posts_per_page' => 5,
		'meta_key'       => 'breaking_news',
		'meta_value'     => 1,
	),
	120
);

if ( empty( $posts ) ) {
	return;
}
?>
<div class="breaking-bar" role="region" aria-label="<?php esc_attr_e( 'Breaking news', 'echoroukonline' ); ?>">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?> breaking-bar__inner">
		<strong><?php esc_html_e( 'Breaking', 'echoroukonline' ); ?></strong>
		<ul>
			<?php foreach ( $posts as $post ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>

