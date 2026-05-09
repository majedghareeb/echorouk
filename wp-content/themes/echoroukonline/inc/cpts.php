<?php
/**
 * Custom post types.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_register_content_cpts() {
	$items = array(
		'video'    => array(
			'singular' => esc_html__( 'Video', 'echoroukonline' ),
			'plural'   => esc_html__( 'Videos', 'echoroukonline' ),
			'icon'     => 'dashicons-video-alt3',
			'has_archive' => true,
			'rewrite_slug' => 'video',
		),
		'gallery'  => array(
			'singular' => esc_html__( 'Gallery', 'echoroukonline' ),
			'plural'   => esc_html__( 'Galleries', 'echoroukonline' ),
			'icon'     => 'dashicons-format-gallery',
			'has_archive' => true,
			'rewrite_slug' => 'gallery',
		),
		'audio'    => array(
			'singular' => esc_html__( 'Audio', 'echoroukonline' ),
			'plural'   => esc_html__( 'Audio', 'echoroukonline' ),
			'icon'     => 'dashicons-controls-volumeon',
			'has_archive' => true,
			'rewrite_slug' => 'audio',
		),
		'document' => array(
			'singular' => esc_html__( 'Document', 'echoroukonline' ),
			'plural'   => esc_html__( 'Documents', 'echoroukonline' ),
			'icon'     => 'dashicons-media-document',
			'has_archive' => true,
			'rewrite_slug' => 'document',
		),
		'live_coverage' => array(
			'singular' => esc_html__( 'Live Coverage', 'echoroukonline' ),
			'plural'   => esc_html__( 'Live Coverage', 'echoroukonline' ),
			'icon'     => 'dashicons-megaphone',
			'has_archive' => false,
			'rewrite_slug' => '',
		),
	);

	foreach ( $items as $post_type => $data ) {
		register_post_type(
			$post_type,
			array(
				'labels'       => echorouk_cpt_labels( $data['singular'], $data['plural'] ),
				'public'       => true,
				'has_archive'  => isset( $data['has_archive'] ) ? (bool) $data['has_archive'] : true,
				'show_in_rest' => true,
				'menu_icon'    => $data['icon'],
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'comments' ),
				'taxonomies'   => array( 'category', 'post_tag' ),
				'rewrite'      => array(
					'slug'       => isset( $data['rewrite_slug'] ) ? (string) $data['rewrite_slug'] : $post_type,
					'with_front' => false,
				),
			)
		);
	}

	register_post_type(
		'guest_author',
		array(
			'labels'       => echorouk_cpt_labels( esc_html__( 'Guest Author', 'echoroukonline' ), esc_html__( 'Guest Authors', 'echoroukonline' ) ),
			'public'       => true,
			'has_archive'  => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-id',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'      => array(
				'slug'       => 'guest-author',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'echorouk_register_content_cpts' );

function echorouk_cpt_labels( $singular, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'add_new_item'       => sprintf( esc_html__( 'Add New %s', 'echoroukonline' ), $singular ),
		'edit_item'          => sprintf( esc_html__( 'Edit %s', 'echoroukonline' ), $singular ),
		'new_item'           => sprintf( esc_html__( 'New %s', 'echoroukonline' ), $singular ),
		'view_item'          => sprintf( esc_html__( 'View %s', 'echoroukonline' ), $singular ),
		'search_items'       => sprintf( esc_html__( 'Search %s', 'echoroukonline' ), $plural ),
		'not_found'          => esc_html__( 'No items found.', 'echoroukonline' ),
		'not_found_in_trash' => esc_html__( 'No items found in Trash.', 'echoroukonline' ),
	);
}
