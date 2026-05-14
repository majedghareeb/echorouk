<?php
/**
 * Native post meta fallback for ACF-compatible field names.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_article_meta_fields() {
	return array(
		'ai_summary'        => array( 'label' => esc_html__( 'AI summary', 'echoroukonline' ), 'type' => 'textarea', 'sanitize' => 'echorouk_sanitize_kses_post', 'rest_type' => 'string' ),
		'tts_url'           => array( 'label' => esc_html__( 'TTS URL', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw', 'rest_type' => 'string' ),
		'reading_time'      => array( 'label' => esc_html__( 'Reading time', 'echoroukonline' ), 'type' => 'number', 'sanitize' => 'absint', 'rest_type' => 'integer' ),
		'main_video_url'    => array( 'label' => esc_html__( 'Main video URL', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw', 'rest_type' => 'string' ),
		'main_audio_url'    => array( 'label' => esc_html__( 'Main audio URL', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw', 'rest_type' => 'string' ),
		'pdf_file'          => array( 'label' => esc_html__( 'PDF file URL', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw', 'rest_type' => 'string' ),
		'source_name'       => array( 'label' => esc_html__( 'Source name', 'echoroukonline' ), 'type' => 'text', 'sanitize' => 'sanitize_text_field', 'rest_type' => 'string' ),
		'source_url'        => array( 'label' => esc_html__( 'Source URL', 'echoroukonline' ), 'type' => 'url', 'sanitize' => 'esc_url_raw', 'rest_type' => 'string' ),
		'editorial_pick'    => array( 'label' => esc_html__( 'Editorial pick', 'echoroukonline' ), 'type' => 'checkbox', 'sanitize' => 'echorouk_sanitize_bool', 'rest_type' => 'boolean' ),
		'featured_priority' => array( 'label' => esc_html__( 'Featured priority', 'echoroukonline' ), 'type' => 'number', 'sanitize' => 'absint', 'rest_type' => 'integer' ),
		'breaking_news'     => array( 'label' => esc_html__( 'Breaking news', 'echoroukonline' ), 'type' => 'checkbox', 'sanitize' => 'echorouk_sanitize_bool', 'rest_type' => 'boolean' ),
		'sponsored_label'   => array( 'label' => esc_html__( 'Sponsored label', 'echoroukonline' ), 'type' => 'text', 'sanitize' => 'sanitize_text_field', 'rest_type' => 'string' ),
	);
}

function echorouk_register_article_meta() {
	foreach ( echorouk_article_post_types() as $post_type ) {
		foreach ( echorouk_article_meta_fields() as $key => $field ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'single'            => true,
					'type'              => $field['rest_type'],
					'show_in_rest'      => true,
					'sanitize_callback' => $field['sanitize'],
					'auth_callback'     => 'echorouk_meta_auth_callback',
				)
			);
		}
	}
}
add_action( 'init', 'echorouk_register_article_meta' );

function echorouk_add_article_meta_boxes() {
	foreach ( echorouk_article_post_types() as $post_type ) {
		add_meta_box(
			'echorouk_article_fields',
			esc_html__( 'Article fields', 'echoroukonline' ),
			'echorouk_render_article_meta_box',
			$post_type,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'echorouk_add_article_meta_boxes' );

function echorouk_enqueue_article_meta_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, echorouk_article_post_types(), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_register_script( 'echorouk-article-meta-admin', '', array(), ECHOROUK_THEME_VERSION, true );
	wp_enqueue_script( 'echorouk-article-meta-admin' );
	wp_add_inline_script(
		'echorouk-article-meta-admin',
		"(function(){\n" .
		"  'use strict';\n" .
		"  function openPdfPicker(button){\n" .
		"    if (typeof wp === 'undefined' || !wp.media) { return; }\n" .
		"    var fieldId = button.getAttribute('data-target');\n" .
		"    if (!fieldId) { return; }\n" .
		"    var input = document.getElementById(fieldId);\n" .
		"    if (!input) { return; }\n" .
		"    var frame = wp.media({\n" .
		"      title: 'Select PDF',\n" .
		"      button: { text: 'Use PDF' },\n" .
		"      library: { type: 'application/pdf' },\n" .
		"      multiple: false\n" .
		"    });\n" .
		"    frame.on('select', function(){\n" .
		"      var selection = frame.state().get('selection').first();\n" .
		"      if (!selection) { return; }\n" .
		"      var data = selection.toJSON();\n" .
		"      if (data && data.url) {\n" .
		"        input.value = data.url;\n" .
		"        input.dispatchEvent(new Event('change', { bubbles: true }));\n" .
		"      }\n" .
		"    });\n" .
		"    frame.open();\n" .
		"  }\n" .
		"  document.addEventListener('click', function(event){\n" .
		"    var button = event.target.closest('.echorouk-pdf-upload');\n" .
		"    if (!button) { return; }\n" .
		"    event.preventDefault();\n" .
		"    openPdfPicker(button);\n" .
		"  });\n" .
		"})();"
	);
}
add_action( 'admin_enqueue_scripts', 'echorouk_enqueue_article_meta_admin_assets' );

function echorouk_render_article_meta_box( $post ) {
	wp_nonce_field( 'echorouk_save_article_meta', 'echorouk_article_meta_nonce' );
	echo '<div class="echorouk-meta-grid">';

	foreach ( echorouk_article_meta_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p class="echorouk-meta-field">';
		echo '<label for="' . esc_attr( 'echorouk_' . $key ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label>';

		if ( 'textarea' === $field['type'] ) {
			printf(
				'<textarea id="%1$s" name="echorouk_meta[%2$s]" rows="4" class="widefat">%3$s</textarea>',
				esc_attr( 'echorouk_' . $key ),
				esc_attr( $key ),
				esc_textarea( $value )
			);
		} elseif ( 'checkbox' === $field['type'] ) {
			printf(
				'<label class="selectit"><input id="%1$s" name="echorouk_meta[%2$s]" type="checkbox" value="1" %3$s> %4$s</label>',
				esc_attr( 'echorouk_' . $key ),
				esc_attr( $key ),
				checked( (bool) $value, true, false ),
				esc_html__( 'Enabled', 'echoroukonline' )
			);
		} elseif ( 'pdf_file' === $key ) {
			$field_id = 'echorouk_' . $key;

			printf(
				'<input id="%1$s" name="echorouk_meta[%2$s]" type="url" value="%3$s" class="widefat">',
				esc_attr( $field_id ),
				esc_attr( $key ),
				esc_attr( $value )
			);
			printf(
				'<button type="button" class="button button-secondary echorouk-pdf-upload" data-target="%1$s" style="margin-top:8px;">%2$s</button>',
				esc_attr( $field_id ),
				esc_html__( 'Upload/Select PDF', 'echoroukonline' )
			);
		} else {
			printf(
				'<input id="%1$s" name="echorouk_meta[%2$s]" type="%3$s" value="%4$s" class="widefat">',
				esc_attr( 'echorouk_' . $key ),
				esc_attr( $key ),
				esc_attr( $field['type'] ),
				esc_attr( $value )
			);
		}

		echo '</p>';
	}

	echo '</div>';
}

function echorouk_save_article_meta( $post_id ) {
	if ( ! in_array( get_post_type( $post_id ), echorouk_article_post_types(), true ) ) {
		return;
	}

	if ( ! isset( $_POST['echorouk_article_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['echorouk_article_meta_nonce'] ) ), 'echorouk_save_article_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! echorouk_can_edit_post( $post_id ) ) {
		return;
	}

	$posted = isset( $_POST['echorouk_meta'] ) && is_array( $_POST['echorouk_meta'] ) ? wp_unslash( $_POST['echorouk_meta'] ) : array();

	foreach ( echorouk_article_meta_fields() as $key => $field ) {
		if ( 'checkbox' === $field['type'] ) {
			update_post_meta( $post_id, $key, ! empty( $posted[ $key ] ) ? 1 : 0 );
			continue;
		}

		if ( ! isset( $posted[ $key ] ) ) {
			continue;
		}

		$callback = $field['sanitize'];
		$value    = is_callable( $callback ) ? call_user_func( $callback, $posted[ $key ] ) : sanitize_text_field( $posted[ $key ] );

		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post', 'echorouk_save_article_meta' );
