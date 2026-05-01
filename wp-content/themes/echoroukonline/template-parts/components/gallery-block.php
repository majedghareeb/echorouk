<?php
/**
 * Attached image gallery block.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$attachments = get_children(
	array(
		'post_parent'    => get_the_ID(),
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	)
);

if ( empty( $attachments ) ) {
	return;
}
?>
<section class="gallery-block">
	<h2><?php esc_html_e( 'Gallery', 'echoroukonline' ); ?></h2>
	<div class="gallery-block__grid">
		<?php foreach ( $attachments as $attachment ) : ?>
			<figure>
				<?php echo wp_get_attachment_image( $attachment->ID, 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
				<?php if ( wp_get_attachment_caption( $attachment->ID ) ) : ?>
					<figcaption><?php echo esc_html( wp_get_attachment_caption( $attachment->ID ) ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</section>

