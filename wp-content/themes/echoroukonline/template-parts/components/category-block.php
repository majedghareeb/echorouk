<?php
/**
 * Category news block.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$categories = get_categories(
	array(
		'number'     => 3,
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);

if ( empty( $categories ) ) {
	return;
}
?>
<section class="section-block category-blocks">
	<div class="section-heading">
		<h2 class="section-title"><?php esc_html_e( 'Sections', 'echoroukonline' ); ?></h2>
	</div>
	<div class="category-blocks__grid">
		<?php foreach ( $categories as $category ) : ?>
			<?php
			$posts = echorouk_get_cached_posts(
				'category_block_' . $category->term_id,
				array(
					'post_type'      => echorouk_news_post_types(),
					'cat'            => $category->term_id,
					'posts_per_page' => 3,
				),
				300
			);
			?>
			<section class="category-block">
				<header class="category-block__header">
					<h3><a href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></h3>
				</header>
				<div class="category-block__posts">
					<?php foreach ( $posts as $post ) : ?>
						<?php echorouk_news_card( $post->ID, 'compact' ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
</section>

