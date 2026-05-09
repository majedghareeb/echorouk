<?php
/**
 * Template routing for organized theme folders.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locate an organized template file, falling back to WordPress' selected file.
 *
 * @param string $relative_path Theme-relative template path.
 * @param string $fallback      Existing template selected by WordPress.
 * @return string
 */
function echorouk_locate_routed_template( $relative_path, $fallback ) {
	$template = locate_template( $relative_path );

	return $template ? $template : $fallback;
}

/**
 * Route single CPT templates out of the theme root.
 *
 * @param string $template Existing template selected by WordPress.
 * @return string
 */
function echorouk_route_single_templates( $template ) {
	$routes = array(
		'audio'        => 'single-templates/audio.php',
		'document'     => 'single-templates/document.php',
		'gallery'      => 'single-templates/gallery.php',
		'guest_author' => 'single-templates/guest-author.php',
		'live_coverage' => 'single-templates/live-coverage.php',
		'video'        => 'single-templates/video.php',
	);

	$post_type = get_post_type();

	if ( isset( $routes[ $post_type ] ) ) {
		return echorouk_locate_routed_template( $routes[ $post_type ], $template );
	}

	return $template;
}
add_filter( 'single_template', 'echorouk_route_single_templates' );

/**
 * Route custom archive templates out of the theme root.
 *
 * @param string $template Existing template selected by WordPress.
 * @return string
 */
function echorouk_route_archive_templates( $template ) {
	if ( is_post_type_archive( 'guest_author' ) ) {
		return echorouk_locate_routed_template( 'archive-templates/guest-author.php', $template );
	}

	return $template;
}
add_filter( 'archive_template', 'echorouk_route_archive_templates' );

/**
 * Keep legacy page-template assignments visible after moving files.
 *
 * @param array<string,string> $templates Available page templates.
 * @return array<string,string>
 */
function echorouk_register_legacy_page_template_choices( $templates ) {
	if ( ! isset( $templates['template-section.php'] ) ) {
		$templates['template-section.php'] = esc_html__( 'Section Landing Page', 'echoroukonline' );
	}

	return $templates;
}
add_filter( 'theme_page_templates', 'echorouk_register_legacy_page_template_choices' );

/**
 * Preserve old page-template assignments after moving the file under page-templates.
 *
 * @param string $template Existing template selected by WordPress.
 * @return string
 */
function echorouk_route_legacy_page_templates( $template ) {
	$routes = array(
		'template-section.php' => 'page-templates/section-landing.php',
	);

	$template_slug = get_page_template_slug( get_queried_object_id() );

	if ( isset( $routes[ $template_slug ] ) ) {
		return echorouk_locate_routed_template( $routes[ $template_slug ], $template );
	}

	return $template;
}
add_filter( 'page_template', 'echorouk_route_legacy_page_templates' );

/**
 * Register a dedicated saved-articles route.
 *
 * @return void
 */
function echorouk_register_saved_articles_route() {
	add_rewrite_rule( '^saved-articles/?$', 'index.php?echorouk_saved_articles=1', 'top' );
}
add_action( 'init', 'echorouk_register_saved_articles_route' );

/**
 * Expose custom query vars.
 *
 * @param array<int,string> $vars Public query vars.
 * @return array<int,string>
 */
function echorouk_register_custom_query_vars( $vars ) {
	$vars[] = 'echorouk_saved_articles';

	return $vars;
}
add_filter( 'query_vars', 'echorouk_register_custom_query_vars' );

/**
 * Route saved-articles URL to its template.
 *
 * Includes a 404 fallback based on request path so it works before rewrite flush.
 *
 * @param string $template Existing template selected by WordPress.
 * @return string
 */
function echorouk_route_saved_articles_template( $template ) {
	$is_saved_route = (bool) get_query_var( 'echorouk_saved_articles' );

	if ( ! $is_saved_route && is_404() ) {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path        = trim( wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
		$home_path   = trim( wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

		if ( $home_path && 0 === strpos( $path, $home_path ) ) {
			$path = trim( substr( $path, strlen( $home_path ) ), '/' );
		}

		if ( 'saved-articles' === $path ) {
			$is_saved_route = true;
			status_header( 200 );
			nocache_headers();

			global $wp_query;
			if ( $wp_query instanceof WP_Query ) {
				$wp_query->is_404 = false;
			}
		}
	}

	if ( $is_saved_route ) {
		return echorouk_locate_routed_template( 'page-templates/saved-articles.php', $template );
	}

	return $template;
}
add_filter( 'template_include', 'echorouk_route_saved_articles_template', 20 );
