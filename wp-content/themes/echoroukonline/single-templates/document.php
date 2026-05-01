<?php
/**
 * Document single template.
 *
 * @package EchouroukOnline
 */

get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content/single-article', null, array( 'format' => 'document' ) );
endwhile;
get_footer();
