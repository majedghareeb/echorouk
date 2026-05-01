<?php
/**
 * Video single template.
 *
 * @package EchouroukOnline
 */

get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content/single-article', null, array( 'format' => 'video' ) );
endwhile;
get_footer();
