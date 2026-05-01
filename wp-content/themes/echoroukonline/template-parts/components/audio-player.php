<?php
/**
 * Audio article player.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$audio_url = get_post_meta( get_the_ID(), 'main_audio_url', true );

if ( ! $audio_url ) {
	return;
}
?>
<section class="media-embed media-embed--audio">
	<h2><?php esc_html_e( 'Audio', 'echoroukonline' ); ?></h2>
	<audio controls preload="none" src="<?php echo esc_url( $audio_url ); ?>"></audio>
</section>

