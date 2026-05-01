<?php
/**
 * Homepage hero lead story.
 *
 * @package EchouroukOnline
 */

defined( 'ABSPATH' ) || exit;

$hero_id = absint( echorouk_get_option( 'hero_post_id', 0 ) );
$hero    = $hero_id ? get_post( $hero_id ) : null;

if ( ! $hero || 'publish' !== $hero->post_status ) {
	$posts = echorouk_get_cached_posts(
		'hero_lead',
		array(
			'post_type'      => echorouk_news_post_types(),
			'posts_per_page' => 1,
			'meta_key'       => 'editorial_pick',
			'meta_value'     => 1,
			'orderby'        => 'date',
		),
		180
	);
	$hero = ! empty( $posts ) ? $posts[0] : null;
}

if ( ! $hero ) {
	return;
}

$secondary = echorouk_get_cached_posts(
	'hero_secondary',
	array(
		'post_type'      => echorouk_news_post_types(),
		'post__not_in'   => array( $hero->ID ),
		'posts_per_page' => 4,
	),
	180
);
?>
<section class="hero-lead">
	<div class="<?php echo esc_attr( echorouk_container_class() ); ?>">
		<div class="hero-lead__grid">
			<article class="hero-lead__main">
				<a class="hero-lead__image-link" href="<?php echo esc_url( get_permalink( $hero ) ); ?>">
					<?php echo echorouk_post_image_html( $hero->ID, 'echorouk-hero', 'hero-lead__image', true ); ?>
				</a>
				<div class="hero-lead__content">
					<?php echorouk_the_category_badge( $hero->ID ); ?>
					<h1><a href="<?php echo esc_url( get_permalink( $hero ) ); ?>"><?php echo esc_html( get_the_title( $hero ) ); ?></a></h1>
					<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $hero ), 28 ) ); ?></p>
				</div>
			</article>

			<div class="hero-lead__side">
				<?php foreach ( $secondary as $post ) : ?>
					<?php echorouk_news_card( $post->ID, 'compact' ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
