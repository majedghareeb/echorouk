<?php
/**
 * Latest news block.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$count = absint( echorouk_get_option( 'latest_news_count', 8 ) );
$posts = echorouk_get_cached_posts(
	'latest_news_' . $count,
	array(
		'post_type'      => echorouk_news_post_types(),
		'posts_per_page' => $count,
	),
	180
);

if ( empty( $posts ) ) {
	return;
}
?>
<section class="section-block latest-news">
	<div class="section-heading">
		<h2 class="section-title"><?php esc_html_e( 'Latest news', 'echoroukonline' ); ?></h2>
	</div>
	<div class="archive-grid">
		<?php foreach ( $posts as $post ) : ?>
			<?php echorouk_news_card( $post->ID ); ?>
		<?php endforeach; ?>
	</div>
</section>

