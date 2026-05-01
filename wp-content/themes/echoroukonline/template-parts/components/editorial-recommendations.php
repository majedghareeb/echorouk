<?php
/**
 * Editorial recommendation widget.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$posts = echorouk_get_cached_posts(
	'editorial_recommendations',
	array(
		'post_type'      => echorouk_news_post_types(),
		'posts_per_page' => 4,
		'meta_key'       => 'editorial_pick',
		'meta_value'     => 1,
		'orderby'        => 'date',
	),
	300
);

if ( empty( $posts ) ) {
	return;
}
?>
<section class="widget-block editorial-widget">
	<h2 class="widget-title"><?php esc_html_e( 'Editorial picks', 'echoroukonline' ); ?></h2>
	<div class="widget-news-list">
		<?php foreach ( $posts as $post ) : ?>
			<?php echorouk_news_card( $post->ID, 'compact' ); ?>
		<?php endforeach; ?>
	</div>
</section>

