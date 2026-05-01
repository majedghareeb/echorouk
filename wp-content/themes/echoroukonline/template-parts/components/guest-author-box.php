<?php
/**
 * Guest author box.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

if ( ! echorouk_get_post_guest_author_id() ) {
	return;
}

get_template_part( 'template-parts/components/author-box' );

