<?php

/**
 * Breaking news bar.
 *
 * @package EchouroukOnline
 */

defined('ABSPATH') || exit;

$ticker_section = function_exists('echorouk_homepage_get_section') ? echorouk_homepage_get_section('news_ticker') : null;
$ticker_meta    = is_array($ticker_section) && ! empty($ticker_section['meta']) && is_array($ticker_section['meta']) ? $ticker_section['meta'] : array();
$show_normal    = ! isset($ticker_meta['show_latest']) || ! empty($ticker_meta['show_latest']);
$show_breaking  = ! isset($ticker_meta['show_breaking']) || ! empty($ticker_meta['show_breaking']);
$limit          = -1;

$posts = function_exists('echorouk_homepage_get_news_ticker_posts')
	? echorouk_homepage_get_news_ticker_posts($limit, $show_normal, $show_breaking)
	: array();

if (empty($posts) || ! is_array($posts)) {
	return;
}

$is_rotating = count($posts) > 1;
?>
<div class="breaking-bar<?php echo $is_rotating ? ' breaking-bar--rotating' : ''; ?>" role="region" aria-label="<?php esc_attr_e('Breaking news', 'echoroukonline'); ?>">
	<div class="<?php echo esc_attr(echorouk_container_class()); ?> breaking-bar__inner">
		<ul class="breaking-bar__list" aria-live="polite" data-news-ticker-list<?php echo $is_rotating ? ' data-ticker-interval="4500"' : ''; ?>>
			<?php foreach ($posts as $index => $ticker_post) : ?>
				<?php
				$type = function_exists('echorouk_homepage_get_news_ticker_type') ? echorouk_homepage_get_news_ticker_type($ticker_post->ID) : 'normal';
				$is_breaking = 'breaking' === $type;
				$is_active   = 0 === (int) $index;
				?>
				<li class="breaking-bar__item breaking-bar__item--<?php echo esc_attr($type); ?><?php echo $is_active ? ' is-active' : ''; ?>"<?php echo $is_active ? ' aria-hidden="false"' : ' aria-hidden="true"'; ?>>
					<strong class="breaking-bar__label"><?php echo esc_html($is_breaking ? __('Breaking', 'echoroukonline') : __('Latest news', 'echoroukonline')); ?></strong>
					<a class="breaking-bar__headline" href="<?php echo esc_url(get_permalink($ticker_post)); ?>">
						<?php echo esc_html(get_the_title($ticker_post)); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
