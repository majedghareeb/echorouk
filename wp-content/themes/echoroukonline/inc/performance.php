<?php
/**
 * Performance-oriented toggles.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_apply_performance_toggles() {
	if ( echorouk_get_option( 'disable_emojis', true ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}

	if ( echorouk_get_option( 'disable_embeds', false ) ) {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}
}
add_action( 'init', 'echorouk_apply_performance_toggles' );

function echorouk_dequeue_unused_block_assets() {
	if ( echorouk_get_option( 'disable_block_library_css', true ) && ! is_admin() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'echorouk_dequeue_unused_block_assets', 100 );

function echorouk_image_loading_attributes( $attr ) {
	if ( ! echorouk_get_option( 'lazy_load_images', true ) ) {
		return $attr;
	}

	if ( empty( $attr['loading'] ) && empty( $attr['fetchpriority'] ) ) {
		$attr['loading'] = 'lazy';
	}

	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'echorouk_image_loading_attributes' );

function echorouk_head_performance_hints() {
	$favicon = echorouk_get_media_option_url( 'favicon' );
	if ( $favicon ) {
		printf( '<link rel="icon" href="%s">' . "\n", esc_url( $favicon ) );
	}

	$main_font = ECHOROUK_THEME_DIR . '/assets/fonts/echorouk-Regular.woff2';
	if ( echorouk_get_option( 'preload_main_font', false ) && file_exists( $main_font ) ) {
		printf(
			'<link rel="preload" href="%1$s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( ECHOROUK_THEME_URI . '/assets/fonts/echorouk-Regular.woff2' )
		);
	}

	if ( echorouk_get_option( 'enable_critical_css', false ) ) {
		echo '<style id="echorouk-critical-css">.site-header{contain:layout style}.skip-link:focus{position:fixed;z-index:10000}</style>' . "\n";
	}
}
add_action( 'wp_head', 'echorouk_head_performance_hints', 1 );
