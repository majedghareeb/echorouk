<?php

/**
 * Shared author profile page template.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;

$profile = isset($args['profile']) && is_array($args['profile']) ? $args['profile'] : array();
$query   = isset($args['query']) && $args['query'] instanceof WP_Query ? $args['query'] : null;

$name   = isset($profile['name']) ? (string) $profile['name'] : '';
$role   = isset($profile['role']) ? (string) $profile['role'] : '';
$bio    = isset($profile['bio']) ? (string) $profile['bio'] : '';
$avatar = isset($profile['avatar_html']) ? (string) $profile['avatar_html'] : '';
$social = isset($profile['social']) && is_array($profile['social']) ? $profile['social'] : array();
$icon_base = trailingslashit(ECHOROUK_THEME_URI . '/assets/icons');
$social_icon_map = array(
	'facebook'  => 'facebook-01-stroke-rounded.svg',
	'twitter'   => 'new-twitter-rectangle-stroke-rounded.svg',
	'x'         => 'new-twitter-rectangle-stroke-rounded.svg',
	'instagram' => 'instagram-stroke-rounded.svg',
	'youtube'   => 'youtube-stroke-rounded.svg',
	'email'     => 'mail-01-stroke-rounded.svg',
	'linkedin'  => 'linkedin-stroke-rounded.svg',
);

$pagination = isset($args['pagination']) && is_array($args['pagination']) ? $args['pagination'] : array();
$paged      = isset($pagination['current']) ? max(1, absint($pagination['current'])) : 1;
$base       = isset($pagination['base']) ? (string) $pagination['base'] : '';
$format     = isset($pagination['format']) ? (string) $pagination['format'] : '';
?>
<main id="primary" class="site-main">
	<div class="<?php echo esc_attr(echorouk_container_class()); ?>">
		<?php get_template_part('template-parts/components/breadcrumbs'); ?>

		<header class="author-profile author-profile--single">
			<div class="row g-4 align-items-center">
				<div class="col-4 col-md-6 col-lg-2">
					<?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
				</div>
				<div class="col-8 col-md-6 col-lg-10">
					<?php if ($name) : ?>
						<h1 class="author-profile__name"><?php echo esc_html($name); ?></h1>
					<?php endif; ?>
					<?php if ($role) : ?>
						<p class="author-profile__role"><?php echo esc_html($role); ?></p>
					<?php endif; ?>
					<?php if ($bio) : ?>
						<div class="author-profile__bio"><?php echo wp_kses_post(wpautop($bio)); ?></div>
					<?php endif; ?>

				</div>
				<?php if (! empty($social)) : ?>
					<div class="author-profile__social">
						<?php foreach ($social as $social_key => $social_item) : ?>
							<?php
							$network = isset($social_item['key']) ? sanitize_key((string) $social_item['key']) : sanitize_key((string) $social_key);
							$label = isset($social_item['label']) ? (string) $social_item['label'] : '';
							$url   = isset($social_item['url']) ? esc_url((string) $social_item['url']) : '';
							if (! $label || ! $url || empty($social_icon_map[$network])) {
								continue;
							}

							$icon_file   = (string) $social_icon_map[$network];
							$is_external = 0 !== strpos($url, 'mailto:');
							?>
							<a class="author-profile__social-link author-profile__social-link--<?php echo esc_attr(sanitize_html_class($network)); ?>"
								href="<?php echo esc_url($url); ?>"
								<?php echo $is_external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
								aria-label="<?php echo esc_attr($label); ?>">
								<img src="<?php echo esc_url($icon_base . $icon_file); ?>" alt="" loading="lazy"
									decoding="async">
								<span class="screen-reader-text"><?php echo esc_html($label); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</header>

		<section class="author-articles-section">
			<h2 class="section-title"><?php esc_html_e('Latest articles', 'echoroukonline'); ?></h2>
			<?php if ($query && $query->have_posts()) : ?>
				<div class="archive-grid author-articles-grid">
					<?php
					while ($query->have_posts()) :
						$query->the_post();
						echorouk_news_card(get_the_ID());
					endwhile;
					wp_reset_postdata();
					?>
				</div>
				<?php
				$pagination_base = $base ? $base : esc_url_raw(add_query_arg('paged', '%#%', home_url('/')));
				$total_pages     = max(1, (int) $query->max_num_pages);

				if ($total_pages > 1) :
					$pagination_links = paginate_links(
						array(
							'base'      => $pagination_base,
							'format'    => $format,
							'current'   => $paged,
							'total'     => $total_pages,
							'type'      => 'array',
							'mid_size'  => 1,
							'end_size'  => 1,
							'prev_next' => true,
							'prev_text' => esc_html__('Previous', 'echoroukonline'),
							'next_text' => esc_html__('Next', 'echoroukonline'),
						)
					);

					if (is_array($pagination_links) && ! empty($pagination_links)) :
						$first_page_url = str_replace('%#%', '1', $pagination_base);
						$last_page_url  = str_replace('%#%', (string) $total_pages, $pagination_base);
				?>
						<nav class="category-pagination-wrap" aria-label="<?php esc_attr_e('Author pages', 'echoroukonline'); ?>">
							<div class="category-pagination-meta">
								<?php
								printf(
									esc_html__('Page %1$d of %2$d', 'echoroukonline'),
									(int) $paged,
									(int) $total_pages
								);
								?>
							</div>
							<ul class="category-pagination">
								<?php if ($paged > 1) : ?>
									<li class="category-pagination__item category-pagination__item--jump">
										<a
											href="<?php echo esc_url($first_page_url); ?>"><?php esc_html_e('First', 'echoroukonline'); ?></a>
									</li>
								<?php endif; ?>

								<?php foreach ($pagination_links as $link) : ?>
									<li class="category-pagination__item"><?php echo wp_kses_post($link); ?></li>
								<?php endforeach; ?>

								<?php if ($paged < $total_pages) : ?>
									<li class="category-pagination__item category-pagination__item--jump">
										<a
											href="<?php echo esc_url($last_page_url); ?>"><?php esc_html_e('Last', 'echoroukonline'); ?></a>
									</li>
								<?php endif; ?>
							</ul>
						</nav>
				<?php
					endif;
				endif;
				?>
			<?php else : ?>
				<?php get_template_part('template-parts/content/none'); ?>
			<?php endif; ?>
		</section>
	</div>
</main>