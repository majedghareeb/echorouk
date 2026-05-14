<?php
/**
 * Guest author single template.
 *
 * @package EchouroukOnline
 */

get_header();

while ( have_posts() ) :
	the_post();

	$guest_id      = get_the_ID();
	$guest_name    = get_the_title();
	$guest_role    = (string) get_post_meta( $guest_id, 'guest_job_title', true );
	$guest_bio_raw = (string) get_post_field( 'post_content', $guest_id );
	$guest_bio     = wp_trim_words( wp_strip_all_tags( $guest_bio_raw ), 60 );
	$current_page  = isset( $_GET['author_page'] ) ? max( 1, absint( wp_unslash( $_GET['author_page'] ) ) ) : 1;

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

	$guest_posts_query = new WP_Query(
		array(
			'post_type'      => echorouk_article_post_types(),
			'post_status'    => 'publish',
			'posts_per_page' => 9,
			'paged'          => $current_page,
			'meta_key'       => 'guest_author_id',
			'meta_value'     => $guest_id,
		)
	);

	get_template_part(
		'template-parts/pages/author-profile',
		'',
		array(
			'profile'    => array(
				'name'        => $guest_name,
				'role'        => $guest_role,
				'bio'         => $guest_bio,
				'avatar_html' => echorouk_post_image_html( $guest_id, 'thumbnail', 'author-profile__avatar', true ),
				'social'      => $guest_social,
			),
			'query'      => $guest_posts_query,
			'pagination' => array(
				'current' => $current_page,
				'base'    => esc_url_raw( add_query_arg( 'author_page', '%#%', home_url( '/' ) ) ),
				'format'  => '',
			),
		)
	);

	wp_reset_postdata();
endwhile;

get_footer();
