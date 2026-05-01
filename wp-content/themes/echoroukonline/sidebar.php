<?php
/**
 * Sidebar.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

if ( 'none' === echorouk_get_option( 'sidebar_position', 'right' ) ) {
	return;
}

$sidebar_id = is_singular( echorouk_article_post_types() ) ? 'sidebar-article' : 'sidebar-main';
?>
<aside class="site-sidebar<?php echo echorouk_get_option( 'enable_sticky_ad_sidebar', true ) ? ' site-sidebar--sticky' : ''; ?>" aria-label="<?php esc_attr_e( 'Sidebar', 'echoroukonline' ); ?>">
	<?php echorouk_the_ad_slot( 'sidebar_ad' ); ?>
	<?php if ( is_active_sidebar( $sidebar_id ) ) : ?>
		<?php dynamic_sidebar( $sidebar_id ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/components/most-read' ); ?>
		<?php get_template_part( 'template-parts/components/editorial-recommendations' ); ?>
	<?php endif; ?>
</aside>
