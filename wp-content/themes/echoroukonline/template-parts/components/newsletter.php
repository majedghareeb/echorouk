<?php
/**
 * Newsletter subscription form placeholder.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="newsletter-block">
	<h2><?php esc_html_e( 'Newsletter', 'echoroukonline' ); ?></h2>
	<?php $feedback = function_exists( 'echorouk_newsletter_get_feedback' ) ? echorouk_newsletter_get_feedback() : null; ?>
	<?php if ( is_array( $feedback ) && ! empty( $feedback['message'] ) ) : ?>
		<div class="newsletter-feedback newsletter-feedback--<?php echo esc_attr( $feedback['type'] ); ?>" role="status">
			<?php echo esc_html( $feedback['message'] ); ?>
		</div>
	<?php endif; ?>
	<form class="newsletter-form" method="post" action="<?php echo esc_url( function_exists( 'echorouk_newsletter_form_action_url' ) ? echorouk_newsletter_form_action_url() : admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'echorouk_newsletter_signup', 'echorouk_newsletter_nonce' ); ?>
		<?php if ( function_exists( 'echorouk_newsletter_use_internal_endpoint' ) ? echorouk_newsletter_use_internal_endpoint() : true ) : ?>
			<input type="hidden" name="action" value="echorouk_newsletter_subscribe">
		<?php endif; ?>
		<label class="screen-reader-text" for="echorouk-newsletter-email"><?php esc_html_e( 'Email address', 'echoroukonline' ); ?></label>
		<input id="echorouk-newsletter-email" type="email" name="email" placeholder="<?php esc_attr_e( 'Email address', 'echoroukonline' ); ?>" required>
		<button type="submit"><?php esc_html_e( 'Subscribe', 'echoroukonline' ); ?></button>
	</form>
</section>
