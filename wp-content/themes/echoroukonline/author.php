<?php
/**
 * WordPress author archive.
 *
 * @package EchouroukOnline
 */

get_header();

$author_id      = get_queried_object_id();
$author_name    = get_the_author_meta( 'display_name', $author_id );
$author_bio_raw = (string) get_the_author_meta( 'description', $author_id );
$author_bio     = wp_trim_words( wp_strip_all_tags( $author_bio_raw ), 60 );
$author_role    = (string) get_the_author_meta( 'job_title', $author_id );
$author_website = (string) get_the_author_meta( 'user_url', $author_id );
$paged          = max( 1, (int) get_query_var( 'paged', 1 ), (int) get_query_var( 'page', 1 ) );

$social_keys = array(
	'facebook'  => esc_html__( 'Facebook', 'echoroukonline' ),
	'twitter'   => esc_html__( 'X', 'echoroukonline' ),
	'instagram' => esc_html__( 'Instagram', 'echoroukonline' ),
	'youtube'   => esc_html__( 'YouTube', 'echoroukonline' ),
	'telegram'  => esc_html__( 'Telegram', 'echoroukonline' ),
	'linkedin'  => esc_html__( 'LinkedIn', 'echoroukonline' ),
);

$author_social = array();

if ( $author_website ) {
	$author_social['website'] = array(
		'label' => esc_html__( 'Website', 'echoroukonline' ),
		'url'   => esc_url_raw( $author_website ),
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
		'label' => $label,
		'url'   => $url,
	);
}

$author_posts_query = new WP_Query(
	array(
		'post_type'           => echorouk_article_post_types(),
		'post_status'         => 'publish',
		'author'              => $author_id,
		'posts_per_page'      => 9,
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
	)
);

get_template_part(
	'template-parts/pages/author-profile',
	null,
	array(
		'profile'    => array(
			'name'        => $author_name,
			'role'        => $author_role,
			'bio'         => $author_bio,
			'avatar_html' => get_avatar( $author_id, 180, '', '', array( 'class' => 'author-profile__avatar' ) ),
			'social'      => $author_social,
		),
		'query'      => $author_posts_query,
		'pagination' => array(
			'current' => $paged,
			'base'    => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
			'format'  => '',
		),
	)
);

wp_reset_postdata();
get_footer();
