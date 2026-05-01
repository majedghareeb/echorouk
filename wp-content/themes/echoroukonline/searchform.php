<?php
/**
 * Search form.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="s"><?php esc_html_e( 'Search for:', 'echoroukonline' ); ?></label>
	<input type="search" id="s" class="search-field" placeholder="<?php esc_attr_e( 'Search', 'echoroukonline' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button class="search-submit" type="submit">
		<?php echo echorouk_svg_icon( 'search' ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Search', 'echoroukonline' ); ?></span>
	</button>
</form>

