<?php
/**
 * Standard news article.
 *
 * @package EchouroukOnline
 */

get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content/single-article', null, array( 'format' => 'standard' ) );
endwhile;
get_footer();

