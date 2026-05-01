<?php
/**
 * Video embed component.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$video_url = get_post_meta( get_the_ID(), 'main_video_url', true );

if ( ! $video_url ) {
	return;
}

$embed = wp_oembed_get( $video_url );
?>
<section class="media-embed media-embed--video">
	<h2><?php esc_html_e( 'Video', 'echoroukonline' ); ?></h2>
	<div class="ratio ratio-16x9">
		<?php
		if ( $embed ) {
			$allowed_html           = wp_kses_allowed_html( 'post' );
			$allowed_html['iframe'] = array(
				'allow'           => true,
				'allowfullscreen' => true,
				'frameborder'     => true,
				'height'          => true,
				'loading'         => true,
				'referrerpolicy'  => true,
				'src'             => true,
				'title'           => true,
				'width'           => true,
			);
			echo wp_kses( $embed, $allowed_html );
		} else {
			printf( '<video controls preload="metadata" src="%s"></video>', esc_url( $video_url ) );
		}
		?>
	</div>
</section>
