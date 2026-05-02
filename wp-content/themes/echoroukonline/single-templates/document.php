<?php
/**
 * Document single template.
 *
 * @package EchouroukOnline
 */

get_header();
if ( echorouk_setup_singular_post_context() ) :
	get_template_part( 'template-parts/content/single-article', null, array( 'format' => 'document' ) );
	wp_reset_postdata();
endif;
get_footer();
