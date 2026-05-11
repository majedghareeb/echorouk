<?php
/**
 * Audio single template.
 *
 * @package EchouroukOnline
 */

get_header();
if ( echorouk_setup_singular_post_context() ) :
	get_template_part( 'template-parts/content/single-article', '', array( 'format' => 'audio' ) );
	wp_reset_postdata();
endif;
get_footer();
