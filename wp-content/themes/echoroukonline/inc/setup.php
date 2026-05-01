<?php
/**
 * Theme setup.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_setup() {
	load_theme_textdomain( 'echoroukonline', ECHOROUK_THEME_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-line-height' );
	add_theme_support( 'custom-spacing' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'wp-block-styles' );

	add_editor_style( array( 'assets/css/main.css', 'assets/css/theme.css' ) );

	add_image_size( 'echorouk-card', 420, 236, true );
	add_image_size( 'echorouk-hero', 1200, 675, true );
	add_image_size( 'echorouk-wide', 900, 506, true );

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary menu', 'echoroukonline' ),
			'footer'  => esc_html__( 'Footer menu', 'echoroukonline' ),
			'social'  => esc_html__( 'Social links menu', 'echoroukonline' ),
		)
	);
}
add_action( 'after_setup_theme', 'echorouk_setup' );

function echorouk_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'echorouk/content_width', 1200 );
}
add_action( 'after_setup_theme', 'echorouk_content_width', 0 );
