<?php
/**
 * Video list block.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$posts = echorouk_homepage_section_posts(
	'video',
	4,
	array(
		'post_type'      => 'video',
		'posts_per_page' => 4,
	)
);

if ( empty( $posts ) ) {
	return;
}
?>
<section class="section-block video-list">
	<h2 class="section-title"><?php esc_html_e( 'Videos', 'echoroukonline' ); ?></h2>
	<div class="archive-grid">
		<?php foreach ( $posts as $post ) : ?>
			<?php echorouk_news_card( $post->ID ); ?>
		<?php endforeach; ?>
	</div>
</section>
