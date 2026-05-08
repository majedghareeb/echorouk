<?php
/**
 * Newsletter helpers and submit endpoint.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_newsletter_use_internal_endpoint() {
	$external = trim( (string) echorouk_get_option( 'newsletter_external_action_url', '' ) );

	return '' === $external;
}

function echorouk_newsletter_form_action_url() {
	if ( echorouk_newsletter_use_internal_endpoint() ) {
		return admin_url( 'admin-post.php' );
	}

	$external = esc_url_raw( trim( (string) echorouk_get_option( 'newsletter_external_action_url', '' ) ) );

	return $external ? $external : admin_url( 'admin-post.php' );
}

function echorouk_newsletter_get_feedback() {
	$status = isset( $_GET['echorouk_newsletter'] ) ? sanitize_key( wp_unslash( $_GET['echorouk_newsletter'] ) ) : '';

	if ( '' === $status ) {
		return null;
	}

	$map = array(
		'success'         => array(
			'type'    => 'success',
			'message' => __( 'Subscription successful. Thank you.', 'echoroukonline' ),
		),
		'invalid_email'   => array(
			'type'    => 'error',
			'message' => __( 'Please enter a valid email address.', 'echoroukonline' ),
		),
		'invalid_request' => array(
			'type'    => 'error',
			'message' => __( 'Invalid request. Please try again.', 'echoroukonline' ),
		),
		'failed'          => array(
			'type'    => 'error',
			'message' => __( 'Subscription failed. Please try again later.', 'echoroukonline' ),
		),
	);

	return isset( $map[ $status ] ) ? $map[ $status ] : null;
}

function echorouk_newsletter_redirect_url( $status ) {
	$redirect = wp_get_referer();
	$redirect = $redirect ? $redirect : home_url( '/' );
	$redirect = remove_query_arg( 'echorouk_newsletter', $redirect );

	return add_query_arg(
		array(
			'echorouk_newsletter' => sanitize_key( $status ),
		),
		$redirect
	);
}

function echorouk_handle_newsletter_subscribe() {
	if ( ! isset( $_POST['echorouk_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['echorouk_newsletter_nonce'] ) ), 'echorouk_newsletter_signup' ) ) {
		wp_safe_redirect( echorouk_newsletter_redirect_url( 'invalid_request' ) );
		exit;
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( echorouk_newsletter_redirect_url( 'invalid_email' ) );
		exit;
	}

	$context = array(
		'referer'    => wp_get_referer(),
		'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
	);

	/**
	 * Hook for external newsletter integrations.
	 *
	 * @param string $email Newsletter email.
	 * @param array  $context Request context.
	 */
	do_action( 'echorouk_newsletter_subscribe', $email, $context );

	$subscribers = get_option( 'echorouk_newsletter_subscribers', array() );
	if ( ! is_array( $subscribers ) ) {
		$subscribers = array();
	}

	$updated = false;
	if ( ! in_array( $email, $subscribers, true ) ) {
		$subscribers[] = $email;
		if ( count( $subscribers ) > 2000 ) {
			$subscribers = array_slice( $subscribers, -2000 );
		}
		$updated = update_option( 'echorouk_newsletter_subscribers', array_values( array_unique( $subscribers ) ), false );
	} else {
		$updated = true;
	}

	if ( ! $updated ) {
		wp_safe_redirect( echorouk_newsletter_redirect_url( 'failed' ) );
		exit;
	}

	wp_safe_redirect( echorouk_newsletter_redirect_url( 'success' ) );
	exit;
}
add_action( 'admin_post_nopriv_echorouk_newsletter_subscribe', 'echorouk_handle_newsletter_subscribe' );
add_action( 'admin_post_echorouk_newsletter_subscribe', 'echorouk_handle_newsletter_subscribe' );
