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
$is_article = 'sidebar-article' === $sidebar_id;
$show_article_most_read = ! $is_article || echorouk_get_option( 'show_article_most_read_widget', true );
$sidebars   = wp_get_sidebars_widgets();
$widget_ids = isset( $sidebars[ $sidebar_id ] ) && is_array( $sidebars[ $sidebar_id ] ) ? array_filter( $sidebars[ $sidebar_id ] ) : array();
?>
<aside class="site-sidebar<?php echo echorouk_get_option( 'enable_sticky_ad_sidebar', true ) ? ' site-sidebar--sticky' : ''; ?>" aria-label="<?php esc_attr_e( 'Sidebar', 'echoroukonline' ); ?>">
	<?php echorouk_the_ad_slot( 'sidebar_ad' ); ?>
	<?php if ( is_active_sidebar( $sidebar_id ) ) : ?>
		<?php dynamic_sidebar( $sidebar_id ); ?>
		<?php if ( $is_article && count( $widget_ids ) < 2 ) : ?>
			<?php if ( $show_article_most_read ) : ?>
				<?php get_template_part( 'template-parts/components/most-read' ); ?>
			<?php endif; ?>
			<?php get_template_part( 'template-parts/components/editorial-recommendations' ); ?>
		<?php endif; ?>
	<?php else : ?>
		<?php if ( $show_article_most_read ) : ?>
			<?php get_template_part( 'template-parts/components/most-read' ); ?>
		<?php endif; ?>
		<?php get_template_part( 'template-parts/components/editorial-recommendations' ); ?>
	<?php endif; ?>
</aside>
