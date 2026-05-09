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
		'guest_facebook'  => esc_html__( 'Facebook', 'echoroukonline' ),
		'guest_twitter'   => esc_html__( 'X', 'echoroukonline' ),
		'guest_instagram' => esc_html__( 'Instagram', 'echoroukonline' ),
		'guest_youtube'   => esc_html__( 'YouTube', 'echoroukonline' ),
	);

	$guest_social = array();
	foreach ( $social_map as $meta_key => $label ) {
		$url = esc_url_raw( (string) get_post_meta( $guest_id, $meta_key, true ) );
		if ( ! $url ) {
			continue;
		}

		$guest_social[ $meta_key ] = array(
			'label' => $label,
			'url'   => $url,
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
		null,
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
				'base'    => esc_url_raw( add_query_arg( 'author_page', '%#%' ) ),
				'format'  => '',
			),
		)
	);

	wp_reset_postdata();
endwhile;

get_footer();
