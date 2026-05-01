<?php
/**
 * Template helper tags.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_the_post_meta( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$author  = echorouk_get_post_author_data( $post_id );

	echo '<div class="post-meta">';
	printf(
		'<a class="post-meta__author" href="%1$s">%2$s</a>',
		esc_url( $author['url'] ),
		esc_html( $author['name'] )
	);

	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C, $post_id ) ),
		esc_html( get_the_date( '', $post_id ) )
	);

	if ( echorouk_get_option( 'show_reading_time', true ) ) {
		printf(
			'<span>%s</span>',
			esc_html( sprintf( _n( '%d min read', '%d min read', echorouk_reading_time( $post_id ), 'echoroukonline' ), echorouk_reading_time( $post_id ) ) )
		);
	}

	echo '</div>';
}

function echorouk_the_category_badge( $post_id = 0 ) {
	$category = echorouk_get_primary_category( $post_id );

	if ( ! $category ) {
		return;
	}

	printf(
		'<a class="category-badge" href="%1$s">%2$s</a>',
		esc_url( get_category_link( $category ) ),
		esc_html( $category->name )
	);
}

function echorouk_the_ad_slot( $slot ) {
	$content = echorouk_get_option( $slot, '' );

	if ( empty( $content ) ) {
		return;
	}

	printf( '<div class="ad-slot ad-slot--%1$s">%2$s</div>', esc_attr( $slot ), wp_kses_post( $content ) );
}

function echorouk_the_posts_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 2,
			'prev_text'          => esc_html__( 'Previous', 'echoroukonline' ),
			'next_text'          => esc_html__( 'Next', 'echoroukonline' ),
			'screen_reader_text' => esc_html__( 'Posts navigation', 'echoroukonline' ),
		)
	);
}

function echorouk_archive_title() {
	if ( is_category() || is_tag() || is_tax() ) {
		single_term_title();
		return;
	}

	if ( is_post_type_archive() ) {
		post_type_archive_title();
		return;
	}

	if ( is_author() ) {
		the_archive_title();
		return;
	}

	the_archive_title();
}

function echorouk_get_related_posts( $post_id = 0, $limit = 4 ) {
	$post_id      = $post_id ? absint( $post_id ) : get_the_ID();
	$category_ids = wp_get_post_categories( $post_id );

	if ( empty( $category_ids ) ) {
		return array();
	}

	return echorouk_get_cached_posts(
		'related_' . $post_id,
		array(
			'post_type'      => echorouk_news_post_types(),
			'post__not_in'   => array( $post_id ),
			'category__in'   => $category_ids,
			'posts_per_page' => absint( $limit ),
		),
		600
	);
}

function echorouk_news_card( $post_id, $variant = 'default', $is_lcp = false ) {
	get_template_part(
		'template-parts/components/news-card',
		null,
		array(
			'post_id' => absint( $post_id ),
			'variant' => sanitize_key( $variant ),
			'is_lcp'  => (bool) $is_lcp,
		)
	);
}

