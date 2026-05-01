<?php
/**
 * Author box.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$author = echorouk_get_post_author_data( get_the_ID() );
?>
<section class="author-box">
	<a href="<?php echo esc_url( $author['url'] ); ?>"><?php echo wp_kses_post( $author['avatar'] ); ?></a>
	<div>
		<h2><a href="<?php echo esc_url( $author['url'] ); ?>"><?php echo esc_html( $author['name'] ); ?></a></h2>
		<?php if ( $author['job_title'] ) : ?>
			<p class="author-box__role"><?php echo esc_html( $author['job_title'] ); ?></p>
		<?php endif; ?>
		<?php if ( $author['bio'] ) : ?>
			<div class="author-box__bio"><?php echo wp_kses_post( wpautop( $author['bio'] ) ); ?></div>
		<?php endif; ?>
	</div>
</section>

