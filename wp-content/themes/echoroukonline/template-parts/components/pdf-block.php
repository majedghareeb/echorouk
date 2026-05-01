<?php
/**
 * PDF viewer/download block.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$pdf_url = get_post_meta( get_the_ID(), 'pdf_file', true );

if ( ! $pdf_url ) {
	return;
}
?>
<section class="pdf-block">
	<h2><?php esc_html_e( 'Document', 'echoroukonline' ); ?></h2>
	<div class="pdf-block__viewer">
		<iframe src="<?php echo esc_url( $pdf_url ); ?>" loading="lazy" title="<?php echo esc_attr( get_the_title() ); ?>"></iframe>
	</div>
	<a class="btn btn-primary" href="<?php echo esc_url( $pdf_url ); ?>" download><?php echo echorouk_svg_icon( 'file' ); ?><?php esc_html_e( 'Download PDF', 'echoroukonline' ); ?></a>
</section>

