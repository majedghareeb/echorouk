<?php
/**
 * Category archive template.
 *
 * @package EchouroukOnline
 */

get_header();
$category_posts = array();

if ( isset( $wp_query ) && $wp_query instanceof WP_Query && is_array( $wp_query->posts ) ) {
	$category_posts = array_values( $wp_query->posts );
}
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<?php get_template_part( 'template-parts/components/breadcrumbs' ); ?>
		<header class="archive-header category-header">
			<h1 class="archive-title category-title"><?php single_cat_title(); ?></h1>
			<?php the_archive_description( '<div class="archive-description category-description">', '</div>' ); ?>
		</header>

		<div class="content-layout">
			<section class="content-main">
				<?php if ( ! empty( $category_posts ) ) : ?>
					<div class="archive-grid category-grid">
						<?php
						foreach ( $category_posts as $post ) :
							if ( ! ( $post instanceof WP_Post ) ) {
								continue;
							}
							setup_postdata( $post );
							echorouk_news_card( get_the_ID() );
						endforeach;
						wp_reset_postdata();
						?>
					</div>
					<?php
					$total_pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 0;
					$current     = max( 1, (int) get_query_var( 'paged', 1 ) );

					if ( $total_pages > 1 ) :
						$links = paginate_links(
							array(
								'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
								'format'    => '',
								'current'   => $current,
								'total'     => $total_pages,
								'type'      => 'array',
								'mid_size'  => 1,
								'end_size'  => 1,
								'prev_next' => true,
								'prev_text' => esc_html__( 'Previous', 'echoroukonline' ),
								'next_text' => esc_html__( 'Next', 'echoroukonline' ),
							)
						);

						if ( is_array( $links ) && ! empty( $links ) ) :
							?>
							<nav class="category-pagination-wrap" aria-label="<?php esc_attr_e( 'Category pages', 'echoroukonline' ); ?>">
								<div class="category-pagination-meta">
									<?php
									printf(
										esc_html__( 'Page %1$d of %2$d', 'echoroukonline' ),
										(int) $current,
										(int) $total_pages
									);
									?>
								</div>
								<ul class="category-pagination">
									<?php if ( $current > 1 ) : ?>
										<li class="category-pagination__item category-pagination__item--jump">
											<a href="<?php echo esc_url( get_pagenum_link( 1 ) ); ?>"><?php esc_html_e( 'First', 'echoroukonline' ); ?></a>
										</li>
									<?php endif; ?>

									<?php foreach ( $links as $link ) : ?>
										<li class="category-pagination__item"><?php echo wp_kses_post( $link ); ?></li>
									<?php endforeach; ?>

									<?php if ( $current < $total_pages ) : ?>
										<li class="category-pagination__item category-pagination__item--jump">
											<a href="<?php echo esc_url( get_pagenum_link( $total_pages ) ); ?>"><?php esc_html_e( 'Last', 'echoroukonline' ); ?></a>
										</li>
									<?php endif; ?>
								</ul>
							</nav>
							<?php
						endif;
					endif;
					?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content/none' ); ?>
				<?php endif; ?>
			</section>
			<?php get_sidebar(); ?>
		</div>
	</div>
</main>
<?php
get_footer();
