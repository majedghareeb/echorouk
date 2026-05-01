<?php
/**
 * Empty state.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="no-results">
	<h2><?php esc_html_e( 'Nothing found', 'echoroukonline' ); ?></h2>
	<p><?php esc_html_e( 'Try another search or browse the latest news.', 'echoroukonline' ); ?></p>
	<?php get_search_form(); ?>
</section>

