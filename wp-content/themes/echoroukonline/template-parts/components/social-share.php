<?php
/**
 * Social share buttons.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$url   = rawurlencode( get_permalink() );
$title = rawurlencode( get_the_title() );
?>
<nav class="social-share" aria-label="<?php esc_attr_e( 'Share article', 'echoroukonline' ); ?>">
	<span><?php echo echorouk_svg_icon( 'share' ); ?><?php esc_html_e( 'Share', 'echoroukonline' ); ?></span>
	<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Facebook', 'echoroukonline' ); ?></a>
	<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'X', 'echoroukonline' ); ?></a>
	<a href="<?php echo esc_url( 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'echoroukonline' ); ?></a>
</nav>

