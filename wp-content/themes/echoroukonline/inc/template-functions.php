<?php
/**
 * Template hooks and front-end document helpers.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

/**
 * Keep filtered post arrays compatible with the WordPress loop.
 *
 * The loop indexes WP_Query::posts by zero-based position. Some filters or
 * object-cache values can leave the array keyed by post ID, which triggers
 * undefined offset warnings when the loop starts.
 *
 * @param array    $posts Retrieved posts.
 * @param WP_Query $query Query object.
 * @return array
 */
function echorouk_normalize_loop_posts( $posts, $query ) {
	if ( ! is_array( $posts ) || array() === $posts ) {
		return $posts;
	}

	$normalized = array_values( $posts );

	if ( $query instanceof WP_Query ) {
		$query->post_count = count( $normalized );
	}

	return $normalized;
}
add_filter( 'the_posts', 'echorouk_normalize_loop_posts', PHP_INT_MAX, 2 );

function echorouk_language_attributes_dir( $output ) {
	if ( false !== strpos( $output, 'dir=' ) ) {
		return $output;
	}

	$dir = is_rtl() || echorouk_is_arabic_locale() ? 'rtl' : 'ltr';

	return trim( $output . ' dir="' . esc_attr( $dir ) . '"' );
}
add_filter( 'language_attributes', 'echorouk_language_attributes_dir', 20 );

function echorouk_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	$classes[] = is_rtl() || echorouk_is_arabic_locale() ? 'is-rtl' : 'is-ltr';

	if ( echorouk_get_option( 'enable_dark_mode', false ) ) {
		$classes[] = 'has-dark-mode';
	}

	if ( echorouk_get_option( 'enable_sticky_header', true ) ) {
		$classes[] = 'has-sticky-header';
	}

	$sidebar_position = echorouk_get_option( 'sidebar_position', 'right' );
	$classes[]        = 'header-layout-' . sanitize_html_class( echorouk_get_option( 'header_layout', 'default' ) );
	$classes[]        = 'footer-layout-' . sanitize_html_class( echorouk_get_option( 'footer_layout', 'columns' ) );
	$classes[]        = 'sidebar-position-' . sanitize_html_class( $sidebar_position );

	if ( 'none' === $sidebar_position || ! echorouk_has_sidebar() ) {
		$classes[] = 'no-sidebar';
	}

	return array_unique( $classes );
}
add_filter( 'body_class', 'echorouk_body_classes' );

function echorouk_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'echorouk_pingback_header' );
