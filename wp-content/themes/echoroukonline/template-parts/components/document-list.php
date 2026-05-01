<?php
/**
 * PDF/document list block.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$posts = echorouk_get_cached_posts(
	'document_list',
	array(
		'post_type'      => 'document',
		'posts_per_page' => 4,
	),
	300
);

if ( empty( $posts ) ) {
	return;
}
?>
<section class="section-block document-list">
	<h2 class="section-title"><?php esc_html_e( 'Documents', 'echoroukonline' ); ?></h2>
	<div class="archive-grid">
		<?php foreach ( $posts as $post ) : ?>
			<?php echorouk_news_card( $post->ID ); ?>
		<?php endforeach; ?>
	</div>
</section>

