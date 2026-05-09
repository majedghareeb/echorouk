<?php
/**
 * Live coverage editor helpers.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_live_coverage_meta_key() {
	return 'live_coverage_updates';
}

function echorouk_register_live_coverage_meta() {
	register_post_meta(
		'live_coverage',
		echorouk_live_coverage_meta_key(),
		array(
			'single'            => true,
			'type'              => 'array',
			'show_in_rest'      => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'                 => 'object',
						'additionalProperties' => false,
						'properties'           => array(
							'time' => array(
								'type' => 'string',
							),
							'text' => array(
								'type' => 'string',
							),
							'url'  => array(
								'type' => 'string',
							),
						),
					),
				),
			),
			'sanitize_callback' => 'echorouk_sanitize_live_coverage_updates',
			'auth_callback'     => 'echorouk_meta_auth_callback',
		)
	);
}
add_action( 'init', 'echorouk_register_live_coverage_meta' );

function echorouk_sanitize_live_coverage_updates( $value ) {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$clean = array();
	foreach ( $value as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$time = isset( $item['time'] ) ? sanitize_text_field( (string) $item['time'] ) : '';
		$text = isset( $item['text'] ) ? sanitize_text_field( (string) $item['text'] ) : '';
		$url  = isset( $item['url'] ) ? esc_url_raw( (string) $item['url'] ) : '';

		if ( '' === $text ) {
			continue;
		}

		$clean[] = array(
			'time' => $time,
			'text' => $text,
			'url'  => $url,
		);
	}

	return $clean;
}

function echorouk_add_live_coverage_meta_box() {
	add_meta_box(
		'echorouk_live_coverage_updates',
		esc_html__( 'Live Coverage Updates', 'echoroukonline' ),
		'echorouk_render_live_coverage_meta_box',
		'live_coverage',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'echorouk_add_live_coverage_meta_box' );

function echorouk_render_live_coverage_meta_box( $post ) {
	wp_nonce_field( 'echorouk_save_live_coverage_updates', 'echorouk_live_coverage_nonce' );

	$updates = get_post_meta( $post->ID, echorouk_live_coverage_meta_key(), true );
	$updates = is_array( $updates ) ? array_values( $updates ) : array();
	?>
	<div id="echorouk-live-updates-wrap">
		<p><?php esc_html_e( 'Add timeline updates in chronological order.', 'echoroukonline' ); ?></p>
		<div id="echorouk-live-updates-list">
			<?php foreach ( $updates as $index => $update ) : ?>
				<div class="echorouk-live-update-row">
					<p>
						<label><strong><?php esc_html_e( 'Timestamp', 'echoroukonline' ); ?></strong></label><br>
						<input type="datetime-local" class="widefat" name="echorouk_live_updates[<?php echo esc_attr( $index ); ?>][time]" value="<?php echo esc_attr( isset( $update['time'] ) ? (string) $update['time'] : '' ); ?>">
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Update text', 'echoroukonline' ); ?></strong></label><br>
						<input type="text" class="widefat" name="echorouk_live_updates[<?php echo esc_attr( $index ); ?>][text]" value="<?php echo esc_attr( isset( $update['text'] ) ? (string) $update['text'] : '' ); ?>">
					</p>
					<p>
						<label><strong><?php esc_html_e( 'Optional URL', 'echoroukonline' ); ?></strong></label><br>
						<input type="url" class="widefat" name="echorouk_live_updates[<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( isset( $update['url'] ) ? (string) $update['url'] : '' ); ?>">
					</p>
					<p><button type="button" class="button-link-delete echorouk-live-update-remove"><?php esc_html_e( 'Remove', 'echoroukonline' ); ?></button></p>
					<hr>
				</div>
			<?php endforeach; ?>
		</div>
		<p><button type="button" class="button" id="echorouk-live-update-add"><?php esc_html_e( 'Add update', 'echoroukonline' ); ?></button></p>
	</div>
	<script>
	(function() {
		var wrap = document.getElementById('echorouk-live-updates-wrap');
		if (!wrap) return;

		var list = document.getElementById('echorouk-live-updates-list');
		var addButton = document.getElementById('echorouk-live-update-add');

		function rowTemplate(index) {
			return '' +
				'<div class="echorouk-live-update-row">' +
					'<p><label><strong><?php echo esc_js( __( 'Timestamp', 'echoroukonline' ) ); ?></strong></label><br><input type="datetime-local" class="widefat" name="echorouk_live_updates[' + index + '][time]"></p>' +
					'<p><label><strong><?php echo esc_js( __( 'Update text', 'echoroukonline' ) ); ?></strong></label><br><input type="text" class="widefat" name="echorouk_live_updates[' + index + '][text]"></p>' +
					'<p><label><strong><?php echo esc_js( __( 'Optional URL', 'echoroukonline' ) ); ?></strong></label><br><input type="url" class="widefat" name="echorouk_live_updates[' + index + '][url]"></p>' +
					'<p><button type="button" class="button-link-delete echorouk-live-update-remove"><?php echo esc_js( __( 'Remove', 'echoroukonline' ) ); ?></button></p>' +
					'<hr>' +
				'</div>';
		}

		function nextIndex() {
			return list.querySelectorAll('.echorouk-live-update-row').length;
		}

		addButton.addEventListener('click', function() {
			var div = document.createElement('div');
			div.innerHTML = rowTemplate(nextIndex());
			list.appendChild(div.firstElementChild);
		});

		wrap.addEventListener('click', function(event) {
			if (!event.target.classList.contains('echorouk-live-update-remove')) {
				return;
			}
			var row = event.target.closest('.echorouk-live-update-row');
			if (row) {
				row.remove();
			}
		});
	})();
	</script>
	<?php
}

function echorouk_save_live_coverage_updates( $post_id ) {
	if ( 'live_coverage' !== get_post_type( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['echorouk_live_coverage_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['echorouk_live_coverage_nonce'] ) ), 'echorouk_save_live_coverage_updates' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! echorouk_can_edit_post( $post_id ) ) {
		return;
	}

	$posted = isset( $_POST['echorouk_live_updates'] ) && is_array( $_POST['echorouk_live_updates'] ) ? wp_unslash( $_POST['echorouk_live_updates'] ) : array();
	$clean  = echorouk_sanitize_live_coverage_updates( $posted );

	if ( empty( $clean ) ) {
		delete_post_meta( $post_id, echorouk_live_coverage_meta_key() );
		return;
	}

	update_post_meta( $post_id, echorouk_live_coverage_meta_key(), array_values( $clean ) );
}
add_action( 'save_post_live_coverage', 'echorouk_save_live_coverage_updates' );
