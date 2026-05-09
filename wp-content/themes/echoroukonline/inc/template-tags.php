<?php
/**
 * Template helper tags.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

function echorouk_the_post_meta( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$author  = echorouk_get_post_author_data( $post_id );

	echo '<div class="post-meta">';
	printf(
		'<a class="post-meta__author" href="%1$s">%2$s</a>',
		esc_url( $author['url'] ),
		esc_html( $author['name'] )
	);

	printf(
		'<time datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C, $post_id ) ),
		esc_html( get_the_date( '', $post_id ) )
	);

	if ( echorouk_get_option( 'show_reading_time', true ) ) {
		printf(
			'<span>%s</span>',
			esc_html( sprintf( _n( '%d min read', '%d min read', echorouk_reading_time( $post_id ), 'echoroukonline' ), echorouk_reading_time( $post_id ) ) )
		);
	}

	echo '</div>';
}

function echorouk_the_category_badge( $post_id = 0 ) {
	$post_id  = $post_id ? absint( $post_id ) : get_the_ID();
	$category = null;

	if ( is_category() ) {
		$current_category_id = (int) get_queried_object_id();
		if ( $current_category_id > 0 && has_category( $current_category_id, $post_id ) ) {
			$current_category = get_category( $current_category_id );
			if ( $current_category instanceof WP_Term && ! is_wp_error( $current_category ) ) {
				$category = $current_category;
			}
		}
	}

	if ( ! $category ) {
		$category = echorouk_get_primary_category( $post_id );
	}

	if ( ! $category ) {
		return;
	}

	printf(
		'<a class="category-badge" href="%1$s">%2$s</a>',
		esc_url( get_category_link( $category ) ),
		esc_html( $category->name )
	);
}

function echorouk_the_ad_slot( $slot ) {
	$content = echorouk_get_option( $slot, '' );

	if ( empty( $content ) ) {
		return;
	}

	printf( '<div class="ad-slot ad-slot--%1$s">%2$s</div>', esc_attr( $slot ), wp_kses_post( $content ) );
}

function echorouk_the_posts_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 2,
			'prev_text'          => esc_html__( 'Previous', 'echoroukonline' ),
			'next_text'          => esc_html__( 'Next', 'echoroukonline' ),
			'screen_reader_text' => esc_html__( 'Posts navigation', 'echoroukonline' ),
		)
	);
}

function echorouk_archive_title() {
	if ( is_category() || is_tag() || is_tax() ) {
		single_term_title();
		return;
	}

	if ( is_post_type_archive() ) {
		post_type_archive_title();
		return;
	}

	if ( is_author() ) {
		the_archive_title();
		return;
	}

	the_archive_title();
}

function echorouk_get_related_posts( $post_id = 0, $limit = 4 ) {
	$post_id      = $post_id ? absint( $post_id ) : get_the_ID();
	$category_ids = wp_get_post_categories( $post_id );

	if ( empty( $category_ids ) ) {
		return array();
	}

	return echorouk_get_cached_posts(
		'related_' . $post_id,
		array(
			'post_type'      => echorouk_news_post_types(),
			'post__not_in'   => array( $post_id ),
			'category__in'   => $category_ids,
			'posts_per_page' => absint( $limit ),
		),
		600
	);
}

function echorouk_news_card( $post_id, $variant = 'default', $is_lcp = false ) {
	get_template_part(
		'template-parts/components/news-card',
		null,
		array(
			'post_id' => absint( $post_id ),
			'variant' => sanitize_key( $variant ),
			'is_lcp'  => (bool) $is_lcp,
		)
	);
}

/**
 * Render share actions for a post.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function echorouk_the_post_share_actions( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$actions = echorouk_get_post_share_actions( $post_id );

	if ( empty( $actions ) || ! is_array( $actions ) ) {
		return;
	}

	$icon_base = trailingslashit( ECHOROUK_THEME_URI . '/assets/icons' );
	$menu_id   = 'single-article-share-menu-' . $post_id;
	?>
	<div class="single-article__share-dropdown" data-share-dropdown aria-label="<?php esc_attr_e( 'Share article', 'echoroukonline' ); ?>">
		<button type="button" class="single-article__action single-article__action--share-toggle" data-share-toggle aria-expanded="false" aria-haspopup="true" aria-controls="<?php echo esc_attr( $menu_id ); ?>">
			<img class="single-article__action-icon" src="<?php echo esc_url( $icon_base . 'share-01-stroke-rounded.svg' ); ?>" alt="">
			<span><?php esc_html_e( 'Share', 'echoroukonline' ); ?></span>
		</button>
		<div id="<?php echo esc_attr( $menu_id ); ?>" class="single-article__share-menu" role="menu" hidden>
			<?php foreach ( $actions as $key => $action ) : ?>
				<?php
				$type  = isset( $action['type'] ) ? (string) $action['type'] : 'external';
				$label = isset( $action['label'] ) ? (string) $action['label'] : '';
				$url   = isset( $action['url'] ) ? (string) $action['url'] : '';
				$icon  = isset( $action['icon'] ) ? (string) $action['icon'] : '';
				$class = 'single-article__share-item single-article__share-item--' . sanitize_html_class( (string) $key );
				?>
				<?php if ( 'external' === $type ) : ?>
					<a class="<?php echo esc_attr( $class ); ?>" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" role="menuitem">
						<?php if ( $icon ) : ?>
							<img class="single-article__action-icon" src="<?php echo esc_url( $icon_base . $icon ); ?>" alt="">
						<?php endif; ?>
						<span><?php echo esc_html( $label ); ?></span>
					</a>
				<?php elseif ( 'copy' === $type ) : ?>
					<button type="button" class="<?php echo esc_attr( $class ); ?>" role="menuitem" data-share-copy data-share-url="<?php echo esc_url( $url ); ?>" data-label-default="<?php echo esc_attr( $label ); ?>" data-label-copied="<?php echo esc_attr__( 'Link copied', 'echoroukonline' ); ?>">
						<?php if ( $icon ) : ?>
							<img class="single-article__action-icon" src="<?php echo esc_url( $icon_base . $icon ); ?>" alt="">
						<?php endif; ?>
						<span><?php echo esc_html( $label ); ?></span>
					</button>
				<?php elseif ( 'save' === $type ) : ?>
					<button type="button" class="<?php echo esc_attr( $class ); ?>" role="menuitem" data-save-article data-post-url="<?php echo esc_url( get_permalink( $post_id ) ); ?>" data-label-default="<?php echo esc_attr( $label ); ?>" data-label-saved="<?php echo esc_attr__( 'Saved', 'echoroukonline' ); ?>" aria-pressed="false">
						<?php if ( $icon ) : ?>
							<img class="single-article__action-icon" src="<?php echo esc_url( $icon_base . $icon ); ?>" alt="">
						<?php endif; ?>
						<span><?php echo esc_html( $label ); ?></span>
					</button>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}
