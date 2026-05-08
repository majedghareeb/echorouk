<?php
/**
 * Echourouk Online theme bootstrap.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

define( 'ECHOROUK_THEME_VERSION', '0.1.0' );
define( 'ECHOROUK_THEME_DIR', get_template_directory() );
define( 'ECHOROUK_THEME_URI', get_template_directory_uri() );

$echorouk_includes = array(
	'inc/helpers.php',
	'inc/newsletter.php',
	'inc/setup.php',
	'inc/template-functions.php',
	'inc/template-routing.php',
	'inc/redux.php',
	'inc/enqueue.php',
	'inc/performance.php',
	'inc/cpts.php',
	'inc/meta.php',
	'inc/guest-authors.php',
	'inc/template-tags.php',
	'inc/widgets.php',
);

foreach ( $echorouk_includes as $echorouk_file ) {
	require_once ECHOROUK_THEME_DIR . '/' . $echorouk_file;
}
