<?php
/**
 * Newsletter subscription form placeholder.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$feedback = function_exists( 'echorouk_newsletter_get_feedback' ) ? echorouk_newsletter_get_feedback() : null;
$copy     = function_exists( 'echorouk_newsletter_copy' ) ? echorouk_newsletter_copy() : array(
	'title'       => esc_html__( 'Newsletter', 'echoroukonline' ),
	'intro'       => esc_html__( 'Subscribe to receive our latest news and analysis.', 'echoroukonline' ),
	'disclaimer'  => esc_html__( 'By subscribing, you agree to our terms and privacy policy.', 'echoroukonline' ),
	'placeholder' => esc_html__( 'Email address', 'echoroukonline' ),
	'button'      => '+',
);
?>
<section class="newsletter-block">
	<div class="row g-4 align-items-center newsletter-block__row">
		<div class="col-12 col-lg-6 newsletter-block__content-col">
			<h2 class="newsletter-block__title"><?php echo esc_html( $copy['title'] ); ?></h2>
			<p class="newsletter-block__intro"><?php echo esc_html( $copy['intro'] ); ?></p>
			<p class="newsletter-block__disclaimer"><?php echo esc_html( $copy['disclaimer'] ); ?></p>
		</div>
		<div class="col-12 col-lg-6 newsletter-block__form-col">
			<?php if ( is_array( $feedback ) && ! empty( $feedback['message'] ) ) : ?>
				<div class="newsletter-feedback newsletter-feedback--<?php echo esc_attr( $feedback['type'] ); ?>" role="status">
					<?php echo esc_html( $feedback['message'] ); ?>
				</div>
			<?php endif; ?>
			<form class="newsletter-form newsletter-form--split" method="post" action="<?php echo esc_url( function_exists( 'echorouk_newsletter_form_action_url' ) ? echorouk_newsletter_form_action_url() : admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'echorouk_newsletter_signup', 'echorouk_newsletter_nonce' ); ?>
				<?php if ( function_exists( 'echorouk_newsletter_use_internal_endpoint' ) ? echorouk_newsletter_use_internal_endpoint() : true ) : ?>
					<input type="hidden" name="action" value="echorouk_newsletter_subscribe">
				<?php endif; ?>
				<button type="submit" class="newsletter-form__submit" aria-label="<?php esc_attr_e( 'Subscribe', 'echoroukonline' ); ?>">
					<span class="newsletter-form__submit-text"><?php echo esc_html( $copy['button'] ); ?></span>
				</button>
				<label class="screen-reader-text" for="echorouk-newsletter-email"><?php esc_html_e( 'Email address', 'echoroukonline' ); ?></label>
				<input id="echorouk-newsletter-email" type="email" name="email" placeholder="<?php echo esc_attr( $copy['placeholder'] ); ?>" required>
			</form>
		</div>
	</div>
</section>
