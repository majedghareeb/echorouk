<?php
/**
 * WordPress author archive.
 *
 * @package EchouroukOnline
 */

get_header();

/**
 * Build a safe pagination base URL without relying on get_pagenum_link().
 *
 * @param string $page_var Page query var name.
 * @return string
 */
function echorouk_author_pagination_base( $page_var = 'paged' ) {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$current_url = $request_uri ? home_url( $request_uri ) : home_url( '/' );
	$clean_url   = remove_query_arg( array( 'paged', 'page', 'author_page' ), $current_url );

	return esc_url_raw( add_query_arg( $page_var, '%#%', $clean_url ) );
}

$queried_object = get_queried_object();
$author_id      = $queried_object instanceof WP_User ? (int) $queried_object->ID : 0;
$queried_id     = get_queried_object_id();
$author_slug    = (string) get_query_var( 'author_name' );
$paged          = max( 1, (int) get_query_var( 'paged', 1 ), (int) get_query_var( 'page', 1 ) );
$profile        = array();
$query          = null;
$pagination     = array();

if ( $author_id > 0 ) {
	$author_name    = get_the_author_meta( 'display_name', $author_id );
	$author_bio_raw = (string) get_the_author_meta( 'description', $author_id );
	$author_bio     = wp_trim_words( wp_strip_all_tags( $author_bio_raw ), 60 );
	$author_role    = (string) get_the_author_meta( 'job_title', $author_id );
	$author_email   = sanitize_email( (string) get_the_author_meta( 'user_email', $author_id ) );

	$social_keys = array(
		'facebook'  => esc_html__( 'Facebook', 'echoroukonline' ),
		'twitter'   => esc_html__( 'X', 'echoroukonline' ),
		'instagram' => esc_html__( 'Instagram', 'echoroukonline' ),
		'youtube'   => esc_html__( 'YouTube', 'echoroukonline' ),
		'linkedin'  => esc_html__( 'LinkedIn', 'echoroukonline' ),
	);

	$author_social = array();

	if ( $author_email ) {
		$author_social['email'] = array(
			'key'   => 'email',
			'label' => esc_html__( 'Email', 'echoroukonline' ),
			'url'   => 'mailto:' . $author_email,
		);
	}

	foreach ( $social_keys as $key => $label ) {
		$value = (string) get_the_author_meta( $key, $author_id );

		if ( '' === trim( $value ) ) {
			continue;
		}

		if ( 0 !== strpos( $value, 'http://' ) && 0 !== strpos( $value, 'https://' ) ) {
			$value = 'https://' . ltrim( $value, '/' );
		}

		$url = esc_url_raw( $value );
		if ( ! $url ) {
			continue;
		}

		$author_social[ $key ] = array(
			'key'   => $key,
			'label' => $label,
			'url'   => $url,
		);
	}

	$profile = array(
		'name'        => $author_name,
		'role'        => $author_role,
		'bio'         => $author_bio,
		'avatar_html' => get_avatar( $author_id, 180, '', '', array( 'class' => 'author-profile__avatar' ) ),
		'social'      => $author_social,
	);

	$query = new WP_Query(
		array(
			'post_type'           => echorouk_article_post_types(),
			'post_status'         => 'publish',
			'author'              => $author_id,
			'posts_per_page'      => 9,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
		)
	);

	$pagination = array(
		'current' => $paged,
		'base'    => echorouk_author_pagination_base( 'paged' ),
		'format'  => '',
	);
} else {
	$guest_author = null;

	if ( is_singular( 'guest_author' ) && $queried_id > 0 ) {
		$guest_post = get_post( $queried_id );
		if ( $guest_post instanceof WP_Post && 'guest_author' === $guest_post->post_type && 'publish' === $guest_post->post_status ) {
			$guest_author = $guest_post;
		}
	} elseif ( '' !== trim( $author_slug ) ) {
		if ( function_exists( 'echorouk_find_guest_author_by_slug' ) ) {
			$guest_author = echorouk_find_guest_author_by_slug( $author_slug );
		} else {
			$guest_author = get_page_by_path( sanitize_title( $author_slug ), OBJECT, 'guest_author' );
		}
	}

	if ( $guest_author instanceof WP_Post && 'publish' === $guest_author->post_status ) {
		$guest_id      = (int) $guest_author->ID;
		$guest_name    = get_the_title( $guest_id );
		$guest_role    = (string) get_post_meta( $guest_id, 'guest_job_title', true );
		$guest_bio_raw = (string) get_post_field( 'post_content', $guest_id );
		$guest_bio     = wp_trim_words( wp_strip_all_tags( $guest_bio_raw ), 60 );
		$query_page    = isset( $_GET['author_page'] ) ? max( 1, absint( wp_unslash( $_GET['author_page'] ) ) ) : 1;
		$current_page  = max( $paged, $query_page );

		$social_map = array(
			'facebook'  => array(
				'meta_key' => 'guest_facebook',
				'label'    => esc_html__( 'Facebook', 'echoroukonline' ),
			),
			'twitter'   => array(
				'meta_key' => 'guest_twitter',
				'label'    => esc_html__( 'X', 'echoroukonline' ),
			),
			'instagram' => array(
				'meta_key' => 'guest_instagram',
				'label'    => esc_html__( 'Instagram', 'echoroukonline' ),
			),
			'youtube'   => array(
				'meta_key' => 'guest_youtube',
				'label'    => esc_html__( 'YouTube', 'echoroukonline' ),
			),
			'linkedin'  => array(
				'meta_key' => 'guest_linkedin',
				'label'    => esc_html__( 'LinkedIn', 'echoroukonline' ),
			),
		);

		$guest_social = array();
		foreach ( $social_map as $network => $social_meta ) {
			$meta_key = isset( $social_meta['meta_key'] ) ? (string) $social_meta['meta_key'] : '';
			$label    = isset( $social_meta['label'] ) ? (string) $social_meta['label'] : '';
			$url      = esc_url_raw( (string) get_post_meta( $guest_id, $meta_key, true ) );
			if ( ! $url ) {
				continue;
			}

			$guest_social[ $network ] = array(
				'key'   => $network,
				'label' => $label,
				'url'   => $url,
			);
		}

		$guest_email = sanitize_email( (string) get_post_meta( $guest_id, 'guest_email', true ) );
		if ( $guest_email ) {
			$guest_social['email'] = array(
				'key'   => 'email',
				'label' => esc_html__( 'Email', 'echoroukonline' ),
				'url'   => 'mailto:' . $guest_email,
			);
		}

		$profile = array(
			'name'        => $guest_name,
			'role'        => $guest_role,
			'bio'         => $guest_bio,
			'avatar_html' => echorouk_post_image_html( $guest_id, 'thumbnail', 'author-profile__avatar', true ),
			'social'      => $guest_social,
		);

		$query = new WP_Query(
			array(
				'post_type'      => echorouk_article_post_types(),
				'post_status'    => 'publish',
				'posts_per_page' => 9,
				'paged'          => $current_page,
				'meta_key'       => 'guest_author_id',
				'meta_value'     => $guest_id,
			)
		);

		$pagination = array(
			'current' => $current_page,
			'base'    => is_author() ? echorouk_author_pagination_base( 'paged' ) : echorouk_author_pagination_base( 'author_page' ),
			'format'  => '',
		);
	}
}

get_template_part(
	'template-parts/pages/author-profile',
	'',
	array(
		'profile'    => $profile,
		'query'      => $query,
		'pagination' => $pagination,
	)
);

wp_reset_postdata();
get_footer();
