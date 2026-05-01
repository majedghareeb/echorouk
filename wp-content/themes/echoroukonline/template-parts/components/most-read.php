<?php
/**
 * Most read widget.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$posts = echorouk_get_cached_posts(
	'most_read',
	array(
		'post_type'      => echorouk_news_post_types(),
		'posts_per_page' => 5,
		'meta_key'       => 'echorouk_view_count',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	),
	300
);

if ( empty( $posts ) ) {
	$posts = echorouk_get_cached_posts(
		'most_read_fallback',
		array(
			'post_type'      => echorouk_news_post_types(),
			'posts_per_page' => 5,
		),
		300
	);
}

if ( empty( $posts ) ) {
	return;
}
?>
<section class="widget-block most-read-widget">
	<h2 class="widget-title"><?php esc_html_e( 'Most read', 'echoroukonline' ); ?></h2>
	<ol class="ranked-list">
		<?php foreach ( $posts as $index => $post ) : ?>
			<li>
				<span><?php echo esc_html( number_format_i18n( $index + 1 ) ); ?></span>
				<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</section>

