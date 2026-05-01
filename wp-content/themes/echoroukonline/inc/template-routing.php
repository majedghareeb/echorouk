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
