<?php
/**
 * TTS player placeholder.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

if ( ! echorouk_get_option( 'show_tts_player', true ) ) {
	return;
}

$tts_url = get_post_meta( get_the_ID(), 'tts_url', true );
?>
<section class="tts-player">
	<h2><?php esc_html_e( 'Listen to article', 'echoroukonline' ); ?></h2>
	<?php if ( $tts_url ) : ?>
		<audio controls preload="none" src="<?php echo esc_url( $tts_url ); ?>"></audio>
	<?php else : ?>
		<p><?php esc_html_e( 'TTS audio placeholder', 'echoroukonline' ); ?></p>
	<?php endif; ?>
</section>

