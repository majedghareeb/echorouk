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
	<form class="newsletter-form" method="post" action="#">
		<?php wp_nonce_field( 'echorouk_newsletter_signup', 'echorouk_newsletter_nonce' ); ?>
		<label class="screen-reader-text" for="echorouk-newsletter-email"><?php esc_html_e( 'Email address', 'echoroukonline' ); ?></label>
		<input id="echorouk-newsletter-email" type="email" name="email" placeholder="<?php esc_attr_e( 'Email address', 'echoroukonline' ); ?>" required>
		<button type="submit"><?php esc_html_e( 'Subscribe', 'echoroukonline' ); ?></button>
	</form>
</section>

