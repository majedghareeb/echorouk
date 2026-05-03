<?php
/**
 * Guest author profile and relationship handling.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_guest_author_meta_fields() {
	return array(
		'guest_job_title' => array( 'label' => esc_html__( 'Job title', 'echoroukonline' ), 'type' => 'text', 'sanitize' => 'sanitize_text_field' ),
		'guest_email'     => array( 'label' => esc_html__( 'Email', 'echoroukonline' ), 'type' => 'email', 'sanitize' => 'sanitize_email' ),
		'guest_facebook'  => array( 'label' => esc_html__( 'Facebook', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
		'guest_twitter'   => array( 'label' => esc_html__( 'X/Twitter', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
		'guest_instagram' => array( 'label' => esc_html__( 'Instagram', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
		'guest_youtube'   => array( 'label' => esc_html__( 'YouTube', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw' ),
	);
}

function echorouk_register_guest_author_meta() {
	foreach ( echorouk_guest_author_meta_fields() as $key => $field ) {
		register_post_meta(
			'guest_author',
			$key,
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
				'sanitize_callback' => $field['sanitize'],
				'auth_callback'     => 'echorouk_meta_auth_callback',
			)
		);
	}

	foreach ( echorouk_article_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			'guest_author_id',
			array(
				'single'            => true,
				'type'              => 'integer',
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => 'echorouk_meta_auth_callback',
			)
		);
	}
}
add_action( 'init', 'echorouk_register_guest_author_meta' );

function echorouk_add_guest_author_meta_boxes() {
	add_meta_box(
		'echorouk_guest_author_details',
		esc_html__( 'Guest author details', 'echoroukonline' ),
		'echorouk_render_guest_author_details_box',
		'guest_author',
		'normal',
		'default'
	);

	foreach ( echorouk_article_post_types() as $post_type ) {
		add_meta_box(
			'echorouk_guest_author_relation',
			esc_html__( 'Guest author', 'echoroukonline' ),
			'echorouk_render_guest_author_relation_box',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'echorouk_add_guest_author_meta_boxes' );

function echorouk_render_guest_author_details_box( $post ) {
	wp_nonce_field( 'echorouk_save_guest_author', 'echorouk_guest_author_nonce' );

	foreach ( echorouk_guest_author_meta_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		printf(
			'<p><label for="%1$s"><strong>%2$s</strong></label><input id="%1$s" name="echorouk_guest_author[%3$s]" type="%4$s" class="widefat" value="%5$s"></p>',
			esc_attr( 'echorouk_' . $key ),
			esc_html( $field['label'] ),
			esc_attr( $key ),
			esc_attr( $field['type'] ),
			esc_attr( $value )
		);
	}

	echo '<p class="description">' . esc_html__( 'Use the featured image as this guest author avatar.', 'echoroukonline' ) . '</p>';
}

function echorouk_render_guest_author_relation_box( $post ) {
	wp_nonce_field( 'echorouk_save_guest_author_relation', 'echorouk_guest_author_relation_nonce' );

	$current = absint( get_post_meta( $post->ID, 'guest_author_id', true ) );
	$authors = get_posts(
		array(
			'post_type'      => 'guest_author',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);

	echo '<select name="echorouk_guest_author_id" class="widefat">';
	echo '<option value="0">' . esc_html__( 'Use WordPress author', 'echoroukonline' ) . '</option>';

	foreach ( $authors as $author ) {
		printf(
			'<option value="%1$d" %2$s>%3$s</option>',
			absint( $author->ID ),
			selected( $current, $author->ID, false ),
			esc_html( get_the_title( $author ) )
		);
	}

	echo '</select>';
}

function echorouk_save_guest_author_meta( $post_id ) {
	if ( 'guest_author' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['echorouk_guest_author_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['echorouk_guest_author_nonce'] ) ), 'echorouk_save_guest_author' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! echorouk_can_edit_post( $post_id ) ) {
		return;
	}

	$posted = isset( $_POST['echorouk_guest_author'] ) && is_array( $_POST['echorouk_guest_author'] ) ? wp_unslash( $_POST['echorouk_guest_author'] ) : array();

	foreach ( echorouk_guest_author_meta_fields() as $key => $field ) {
		$value    = isset( $posted[ $key ] ) ? $posted[ $key ] : '';
		$callback = $field['sanitize'];
		$value    = is_callable( $callback ) ? call_user_func( $callback, $value ) : sanitize_text_field( $value );

		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post', 'echorouk_save_guest_author_meta' );

function echorouk_save_guest_author_relation( $post_id ) {
	if ( ! in_array( get_post_type( $post_id ), echorouk_article_post_types(), true ) ) {
		return;
	}

	if ( ! isset( $_POST['echorouk_guest_author_relation_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['echorouk_guest_author_relation_nonce'] ) ), 'echorouk_save_guest_author_relation' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! echorouk_can_edit_post( $post_id ) ) {
		return;
	}

	$guest_author_id = isset( $_POST['echorouk_guest_author_id'] ) ? absint( $_POST['echorouk_guest_author_id'] ) : 0;

	if ( $guest_author_id ) {
		update_post_meta( $post_id, 'guest_author_id', $guest_author_id );
	} else {
		delete_post_meta( $post_id, 'guest_author_id' );
	}
}
add_action( 'save_post', 'echorouk_save_guest_author_relation' );

function echorouk_get_post_guest_author_id( $post_id = 0 ) {
	$post_id         = $post_id ? absint( $post_id ) : get_the_ID();
	$guest_author_id = absint( get_post_meta( $post_id, 'guest_author_id', true ) );

	return $guest_author_id && 'guest_author' === get_post_type( $guest_author_id ) ? $guest_author_id : 0;
}

function echorouk_get_post_author_data( $post_id = 0 ) {
	$post_id         = $post_id ? absint( $post_id ) : get_the_ID();
	$guest_author_id = echorouk_get_post_guest_author_id( $post_id );

	if ( $guest_author_id ) {
		return array(
			'type'      => 'guest',
			'id'        => $guest_author_id,
			'name'      => get_the_title( $guest_author_id ),
			'url'       => get_permalink( $guest_author_id ),
			'bio'       => get_post_field( 'post_content', $guest_author_id ),
			'job_title' => get_post_meta( $guest_author_id, 'guest_job_title', true ),
			'avatar'    => echorouk_post_image_html( $guest_author_id, 'thumbnail', 'author-box__avatar' ),
		);
	}

	$user_id = (int) get_post_field( 'post_author', $post_id );

	return array(
		'type'      => 'user',
		'id'        => $user_id,
		'name'      => get_the_author_meta( 'display_name', $user_id ),
		'url'       => get_author_posts_url( $user_id ),
		'bio'       => get_the_author_meta( 'description', $user_id ),
		'job_title' => '',
		'avatar'    => get_avatar( $user_id, 96, '', '', array( 'class' => 'author-box__avatar' ) ),
	);
}
