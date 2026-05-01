<?php
/**
 * Front-end assets.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_enqueue_assets() {
	wp_enqueue_style(
		'echorouk-bootstrap-base',
		ECHOROUK_THEME_URI . '/assets/css/main.css',
		array(),
		echorouk_asset_version( 'assets/css/main.css' )
	);

	wp_enqueue_style(
		'echorouk-bootstrap',
		ECHOROUK_THEME_URI . '/assets/vendor/bootstrap/css/bootstrap.rtl.min.css',
		array( 'echorouk-bootstrap-base' ),
		echorouk_asset_version( 'assets/vendor/bootstrap/css/bootstrap.rtl.min.css' )
	);

	wp_enqueue_style(
		'echorouk-bootstrap-icons',
		ECHOROUK_THEME_URI . '/assets/vendor/bootstrap-icons/bootstrap-icons.min.css',
		array( 'echorouk-bootstrap' ),
		echorouk_asset_version( 'assets/vendor/bootstrap-icons/bootstrap-icons.min.css' )
	);

	wp_enqueue_style(
		'echorouk-theme',
		ECHOROUK_THEME_URI . '/assets/css/theme.css',
		array( 'echorouk-bootstrap-icons' ),
		echorouk_asset_version( 'assets/css/theme.css' )
	);

	wp_enqueue_style(
		'echorouk-style',
		get_stylesheet_uri(),
		array( 'echorouk-theme' ),
		file_exists( get_stylesheet_directory() . '/style.css' ) ? (string) filemtime( get_stylesheet_directory() . '/style.css' ) : ECHOROUK_THEME_VERSION
	);

	if ( ! echorouk_get_option( 'disable_bootstrap_js', false ) ) {
		$bootstrap_js = 'assets/vendor/bootstrap/js/bootstrap.bundle.min.js';
		if ( file_exists( ECHOROUK_THEME_DIR . '/' . $bootstrap_js ) ) {
			wp_enqueue_script(
				'echorouk-bootstrap',
				ECHOROUK_THEME_URI . '/' . $bootstrap_js,
				array(),
				echorouk_asset_version( $bootstrap_js ),
				true
			);
		} else {
			$bootstrap_js = 'assets/js/bootstrap.bundle.min.js';
			wp_enqueue_script(
				'echorouk-bootstrap',
				ECHOROUK_THEME_URI . '/' . $bootstrap_js,
				array(),
				echorouk_asset_version( $bootstrap_js ),
				true
			);
		}
	}

	wp_enqueue_script(
		'echorouk-theme',
		ECHOROUK_THEME_URI . '/assets/js/theme.js',
		array(),
		echorouk_asset_version( 'assets/js/theme.js' ),
		true
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'echorouk_enqueue_assets' );

function echorouk_defer_theme_scripts( $tag, $handle ) {
	$defer_handles = array( 'echorouk-theme', 'echorouk-bootstrap' );

	if ( in_array( $handle, $defer_handles, true ) && false === strpos( $tag, ' defer' ) ) {
		return str_replace( ' src=', ' defer src=', $tag );
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'echorouk_defer_theme_scripts', 10, 2 );
