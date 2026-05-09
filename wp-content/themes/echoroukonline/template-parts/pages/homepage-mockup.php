<?php

/**
 * Homepage mockup body content.
 *
 * @package EchouroukOnline
 */

$get_section = static function ($section_id) {
    if (! function_exists('echorouk_homepage_get_section')) {
        return null;
    }

    $section = echorouk_homepage_get_section($section_id);

    return is_array($section) ? $section : null;
};

$get_section_posts = static function ($section_id, $limit = 6, $fallback_args = array()) use ($get_section) {
    $section = $get_section($section_id);
    if (is_array($section) && array_key_exists('enabled', $section) && empty($section['enabled'])) {
        return array();
    }

    if (function_exists('echorouk_homepage_get_posts_for_section')) {
        $posts = echorouk_homepage_get_posts_for_section($section_id, $limit);
        if (! empty($posts) && is_array($posts)) {
            return $posts;
        }
    }

    if (function_exists('echorouk_homepage_section_posts')) {
        return echorouk_homepage_section_posts($section_id, $limit, $fallback_args);
    }

    return get_posts(
        wp_parse_args(
            $fallback_args,
            array(
                'post_type'      => echorouk_news_post_types(),
                'post_status'    => 'publish',
                'posts_per_page' => absint($limit),
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        )
    );
};

$get_posts_by_ids = static function ($ids, $limit = 0) {
    $ids = is_array($ids) ? array_values(array_filter(array_map('absint', $ids))) : array();

    if (empty($ids)) {
        return array();
    }

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'post__in'       => $ids,
        'orderby'        => 'post__in',
        'posts_per_page' => $limit > 0 ? absint($limit) : count($ids),
    );

    return get_posts($args);
};

$filter_posts = static function ($posts) {
    if (! is_array($posts)) {
        return array();
    }

    $filtered = array();
    foreach ($posts as $post) {
        if ($post instanceof WP_Post && 'publish' === $post->post_status) {
            $filtered[] = $post;
        }
    }

    return $filtered;
};

$get_excerpt = static function ($post, $words = 24) {
    $excerpt = get_the_excerpt($post);
    if (! $excerpt) {
        $excerpt = get_post_field('post_content', $post);
    }

    return wp_trim_words(wp_strip_all_tags((string) $excerpt), absint($words));
};

$hero_section = $get_section('hero');
$hero_meta    = is_array($hero_section) && ! empty($hero_section['meta']) && is_array($hero_section['meta']) ? $hero_section['meta'] : array();

$hero_main = null;
if (! empty($hero_meta['main_post_id'])) {
    $hero_main = get_post(absint($hero_meta['main_post_id']));
}
if (! $hero_main || 'publish' !== $hero_main->post_status) {
    $hero_main = null;
}

$hero_feed = $filter_posts($get_section_posts('hero', 6));
if (! $hero_main && ! empty($hero_feed)) {
    $hero_main = $hero_feed[0];
}

$live_enabled = ! empty($hero_meta['live_coverage_enabled']);
$live_id      = ! empty($hero_meta['live_post_id']) ? absint($hero_meta['live_post_id']) : 0;
$live_post    = ($live_enabled && $live_id) ? get_post($live_id) : null;
if (! $live_post || 'publish' !== $live_post->post_status) {
    $live_post = null;
}

$side_ids     = ! empty($hero_meta['side_post_ids']) && is_array($hero_meta['side_post_ids']) ? $hero_meta['side_post_ids'] : array();
$fallback_ids = ! empty($hero_meta['fallback_post_ids']) && is_array($hero_meta['fallback_post_ids']) ? $hero_meta['fallback_post_ids'] : array();
$right_ids    = ($live_post ? $side_ids : $fallback_ids);
$hero_right   = $filter_posts($get_posts_by_ids($right_ids, 3));

if (empty($hero_right)) {
    $fallback_right = array();
    foreach ($hero_feed as $feed_post) {
        if ($hero_main && $hero_main->ID === $feed_post->ID) {
            continue;
        }
        $fallback_right[] = $feed_post;
        if (count($fallback_right) >= 3) {
            break;
        }
    }
    $hero_right = $fallback_right;
}

$live_timeline_items = array();
if ($live_post) {
    $timeline_raw_keys = array(
        'echorouk_live_updates',
        'live_coverage_updates',
        'live_updates',
        'live_timeline',
    );

    foreach ($timeline_raw_keys as $timeline_key) {
        $timeline_raw = get_post_meta($live_post->ID, $timeline_key, true);

        if (is_string($timeline_raw) && '' !== trim($timeline_raw)) {
            $decoded = json_decode($timeline_raw, true);
            if (is_array($decoded)) {
                $timeline_raw = $decoded;
            }
        }

        if (! is_array($timeline_raw) || empty($timeline_raw)) {
            continue;
        }

        foreach ($timeline_raw as $timeline_entry) {
            if (! is_array($timeline_entry)) {
                continue;
            }

            $entry_title = '';
            foreach (array('title', 'text', 'content', 'headline') as $key) {
                if (! empty($timeline_entry[$key]) && is_string($timeline_entry[$key])) {
                    $entry_title = sanitize_text_field($timeline_entry[$key]);
                    break;
                }
            }

            if ('' === $entry_title) {
                continue;
            }

            $entry_time_raw = '';
            foreach (array('time', 'timestamp', 'date') as $key) {
                if (! empty($timeline_entry[$key]) && is_string($timeline_entry[$key])) {
                    $entry_time_raw = sanitize_text_field($timeline_entry[$key]);
                    break;
                }
            }

            $entry_url = ! empty($timeline_entry['url']) ? esc_url_raw((string) $timeline_entry['url']) : get_permalink($live_post);
            $entry_ts = $entry_time_raw ? strtotime($entry_time_raw) : false;
            if (false !== $entry_ts) {
                $entry_time_display = wp_date('H:i', $entry_ts);
                $entry_datetime = gmdate(DATE_W3C, $entry_ts);
            } else {
                $entry_time_display = $entry_time_raw ? $entry_time_raw : get_the_time('H:i', $live_post);
                $entry_datetime = get_post_time(DATE_W3C, false, $live_post);
            }

            $live_timeline_items[] = array(
                'title'    => $entry_title,
                'url'      => $entry_url,
                'time'     => $entry_time_display,
                'datetime' => $entry_datetime,
            );
        }

        if (! empty($live_timeline_items)) {
            break;
        }
    }

    if (empty($live_timeline_items)) {
        $live_timeline_items[] = array(
            'title'    => get_the_title($live_post),
            'url'      => get_permalink($live_post),
            'time'     => get_the_time('H:i', $live_post),
            'datetime' => get_post_time(DATE_W3C, false, $live_post),
        );
    }
}

$hero_tag = '';
if ($hero_main) {
    $hero_categories = get_the_category($hero_main->ID);
    $hero_tag        = ! empty($hero_categories) ? $hero_categories[0]->name : '';
}

$world_section = $get_section('world');
$world_meta    = is_array($world_section) && ! empty($world_section['meta']) && is_array($world_section['meta']) ? $world_section['meta'] : array();
$world_feed    = $filter_posts($get_section_posts('world', 6));

$world_main = null;
if (! empty($world_meta['main_post_id'])) {
    $world_main = get_post(absint($world_meta['main_post_id']));
}
if (! $world_main || 'publish' !== $world_main->post_status) {
    $world_main = ! empty($world_feed) ? $world_feed[0] : null;
}

$world_secondary = array();
if (! empty($world_meta['secondary_post_ids']) && is_array($world_meta['secondary_post_ids'])) {
    $world_secondary = $filter_posts($get_posts_by_ids($world_meta['secondary_post_ids'], 6));
}
if (empty($world_secondary)) {
    foreach ($world_feed as $world_feed_post) {
        if ($world_main && $world_main->ID === $world_feed_post->ID) {
            continue;
        }
        $world_secondary[] = $world_feed_post;
        if (count($world_secondary) >= 4) {
            break;
        }
    }
}
$world_left  = array_slice($world_secondary, 0, 2);
$world_right = array_slice($world_secondary, 2, 2);

$video_posts   = $filter_posts($get_section_posts('video', 8));
$sport_posts   = $filter_posts($get_section_posts('sport', 5));
$economy_posts = $filter_posts($get_section_posts('economy', 5));
$opinion_posts = $filter_posts($get_section_posts('opinion', 8));
$last_posts    = $filter_posts($get_section_posts('last', 12));
$jawaher_posts = $filter_posts($get_section_posts('jawaher', 5));

if (count($video_posts) < 8) {
    $video_fill = $filter_posts(
        get_posts(
            array(
                'post_type'           => array('video', 'post'),
                'post_status'         => 'publish',
                'posts_per_page'      => 8 - count($video_posts),
                'post__not_in'        => array_values(array_filter(array_map(static function ($post) {
                    return $post instanceof WP_Post ? (int) $post->ID : 0;
                }, $video_posts))),
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
            )
        )
    );

    if (! empty($video_fill)) {
        $video_posts = array_values(array_merge($video_posts, $video_fill));
    }
}

$most_read_posts = echorouk_get_cached_posts(
    'homepage_mockup_most_read',
    array(
        'post_type'      => echorouk_news_post_types(),
        'posts_per_page' => 4,
        'meta_key'       => 'echorouk_view_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    ),
    300
);
$most_read_posts = $filter_posts($most_read_posts);
if (empty($most_read_posts)) {
    $most_read_posts = array_slice($last_posts, 0, 4);
}

$floating_section = $get_section('floating_video');
$floating_meta    = is_array($floating_section) && ! empty($floating_section['meta']) && is_array($floating_section['meta']) ? $floating_section['meta'] : array();
$floating_url     = ! empty($floating_meta['video_url']) ? esc_url_raw((string) $floating_meta['video_url']) : '';
$floating_embed   = $floating_url ? wp_oembed_get($floating_url) : '';

$sport_main   = ! empty($sport_posts) ? $sport_posts[0] : null;
$sport_cards  = array_slice($sport_posts, 1, 4);
$economy_main = ! empty($economy_posts) ? $economy_posts[0] : null;
$economy_cards = array_slice($economy_posts, 1, 4);
$jawaher_main = ! empty($jawaher_posts) ? $jawaher_posts[0] : null;
$jawaher_cards = array_slice($jawaher_posts, 1, 4);

$diplomacy_pool = $last_posts;

if (count($diplomacy_pool) < 8) {
    $diplomacy_fill = $filter_posts(
        get_posts(
            array(
                'post_type'           => echorouk_news_post_types(),
                'post_status'         => 'publish',
                'posts_per_page'      => 8 - count($diplomacy_pool),
                'post__not_in'        => array_values(array_filter(array_map(static function ($post) {
                    return $post instanceof WP_Post ? (int) $post->ID : 0;
                }, $diplomacy_pool))),
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
            )
        )
    );

    if (! empty($diplomacy_fill)) {
        $diplomacy_pool = array_values(array_merge($diplomacy_pool, $diplomacy_fill));
    }
}

$diplomacy_main = ! empty($diplomacy_pool) ? $diplomacy_pool[0] : null;

$diplomacy_remaining = $diplomacy_main ? array_slice($diplomacy_pool, 1) : $diplomacy_pool;
$diplomacy_feature   = ! empty($diplomacy_remaining) ? array_shift($diplomacy_remaining) : $diplomacy_main;
$diplomacy_side      = array_slice($diplomacy_remaining, 0, 3);
$diplomacy_bottom    = array_slice($diplomacy_remaining, 3, 3);

if (count($diplomacy_bottom) < 3) {
    $diplomacy_excluded_ids = array();

    foreach (array($diplomacy_main, $diplomacy_feature) as $diplomacy_post_item) {
        if ($diplomacy_post_item instanceof WP_Post) {
            $diplomacy_excluded_ids[] = (int) $diplomacy_post_item->ID;
        }
    }
    foreach ($diplomacy_side as $diplomacy_post_item) {
        if ($diplomacy_post_item instanceof WP_Post) {
            $diplomacy_excluded_ids[] = (int) $diplomacy_post_item->ID;
        }
    }
    foreach ($diplomacy_bottom as $diplomacy_post_item) {
        if ($diplomacy_post_item instanceof WP_Post) {
            $diplomacy_excluded_ids[] = (int) $diplomacy_post_item->ID;
        }
    }

    $diplomacy_bottom_fill = $filter_posts(
        get_posts(
            array(
                'post_type'           => echorouk_news_post_types(),
                'post_status'         => 'publish',
                'posts_per_page'      => 3 - count($diplomacy_bottom),
                'post__not_in'        => array_values(array_unique(array_map('absint', $diplomacy_excluded_ids))),
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
            )
        )
    );

    if (! empty($diplomacy_bottom_fill)) {
        $diplomacy_bottom = array_values(array_merge($diplomacy_bottom, $diplomacy_bottom_fill));
    }
}

$podcast_posts = echorouk_get_cached_posts(
    'homepage_mockup_podcast',
    array(
        'post_type'      => array('audio', 'post'),
        'post_status'    => 'publish',
        'posts_per_page' => 5,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ),
    300
);
$podcast_posts   = $filter_posts($podcast_posts);
$podcast_feature = ! empty($podcast_posts) ? $podcast_posts[0] : null;
$podcast_list    = array_slice($podcast_posts, 1, 3);

$collect_category_ids = static function ($needles) {
    $needles = is_array($needles) ? $needles : array();
    $needles = array_filter(array_map(static function ($value) {
        $value = strtolower(remove_accents((string) $value));

        return trim(preg_replace('/[^a-z0-9]+/', '', $value));
    }, $needles));

    if (empty($needles)) {
        return array();
    }

    $matched = array();
    $terms   = get_categories(
        array(
            'hide_empty' => false,
        )
    );

    foreach ($terms as $term) {
        $name_key = trim(preg_replace('/[^a-z0-9]+/', '', strtolower(remove_accents((string) $term->name))));
        $slug_key = trim(preg_replace('/[^a-z0-9]+/', '', strtolower(remove_accents((string) $term->slug))));

        if (in_array($name_key, $needles, true) || in_array($slug_key, $needles, true)) {
            $matched[] = (int) $term->term_id;
        }
    }

    return array_values(array_unique($matched));
};

$get_posts_by_category_keys = static function ($category_keys, $limit = 3) use ($filter_posts, $collect_category_ids) {
    $category_ids = $collect_category_ids($category_keys);
    if (empty($category_ids)) {
        return array();
    }

    return $filter_posts(
        get_posts(
            array(
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => absint($limit),
                'category__in'        => $category_ids,
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
            )
        )
    );
};

$french_posts  = $get_posts_by_category_keys(array('francais', 'français', 'french', 'fr'));
$english_posts = $get_posts_by_category_keys(array('english', 'anglais', 'en'));

$podcast_primary_url    = echorouk_get_option('podcast_primary_url', '');
$podcast_secondary_url  = echorouk_get_option('podcast_secondary_url', '');
$podcast_soundcloud_url = echorouk_get_option('podcast_soundcloud_url', '');
$podcast_archive_url    = echorouk_get_option('podcast_archive_url', '');

if (! $podcast_primary_url) {
    $podcast_primary_url = echorouk_get_option('youtube', '');
}
if (! $podcast_secondary_url) {
    $podcast_secondary_url = echorouk_get_option('rss', get_bloginfo('rss2_url'));
}
if (! $podcast_soundcloud_url) {
    $podcast_soundcloud_url = echorouk_get_option('telegram', '');
}
if (! $podcast_archive_url) {
    $podcast_archive_url = home_url('/');
}

$newsletter_feedback = function_exists('echorouk_newsletter_get_feedback') ? echorouk_newsletter_get_feedback() : null;
$newsletter_action   = function_exists('echorouk_newsletter_form_action_url') ? echorouk_newsletter_form_action_url() : admin_url('admin-post.php');
$newsletter_internal = function_exists('echorouk_newsletter_use_internal_endpoint') ? echorouk_newsletter_use_internal_endpoint() : true;
?>
<main id="primary" class="site-main echorouk-homepage-mockup">
    <div class="container-xl echorouk-homepage-wrap py-4">
        <section class="hero grid-border">
            <div class="row g-4 align-items-stretch hero-layout">
                <aside class="col-lg-3 order-3 order-lg-3 hero-col-right">
                    <div class="hero-latest-panel hero-col-card">
                        <?php if (! empty($hero_right)) : ?>
                            <?php $feature = $hero_right[0]; ?>
                            <article class="hero-latest-feature">
                                <a
                                    href="<?php echo esc_url(get_permalink($feature)); ?>"><?php echo echorouk_post_image_html($feature->ID, 'large'); ?></a>
                                <div class="hero-latest-date"><?php echo esc_html(get_the_date('Y/m/d', $feature)); ?></div>
                                <h3><a
                                        href="<?php echo esc_url(get_permalink($feature)); ?>"><?php echo esc_html(get_the_title($feature)); ?></a>
                                </h3>
                            </article>

                            <?php foreach (array_slice($hero_right, 1, 2) as $hero_side_post) : ?>
                                <article class="hero-latest-item">
                                    <a
                                        href="<?php echo esc_url(get_permalink($hero_side_post)); ?>"><?php echo echorouk_post_image_html($hero_side_post->ID, 'thumbnail'); ?></a>
                                    <div>
                                        <h4><a
                                                href="<?php echo esc_url(get_permalink($hero_side_post)); ?>"><?php echo esc_html(get_the_title($hero_side_post)); ?></a>
                                        </h4>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </aside>

                <section class="col-lg-5 order-1 order-lg-2 hero-col-center">
                    <?php if ($hero_main) : ?>
                        <article class="hero-lead hero-col-card">
                            <div class="hero-lead-media position-relative">
                                <span
                                    class="tag"><?php echo esc_html($hero_tag ? $hero_tag : __('العالم', 'echoroukonline')); ?></span>
                                <a
                                    href="<?php echo esc_url(get_permalink($hero_main)); ?>"><?php echo echorouk_post_image_html($hero_main->ID, 'echorouk-hero', '', true); ?></a>
                                <a href="<?php echo esc_url(get_permalink($hero_main)); ?>" class="hero-play-center"
                                    aria-label="<?php esc_attr_e('Read article', 'echoroukonline'); ?>">
                                    <img src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/play-circle-stroke-rounded-white.svg'); ?>"
                                        alt="">
                                </a>

                                <?php if ($floating_url) : ?>
                                    <aside class="hero-floating-video"
                                        aria-label="<?php esc_attr_e('Floating video', 'echoroukonline'); ?>">
                                        <div class="hero-floating-head">
                                            <button class="hero-floating-close" type="button"
                                                aria-label="<?php esc_attr_e('Close floating video', 'echoroukonline'); ?>">×</button>
                                            <span
                                                class="hero-floating-live"><?php esc_html_e('Live Streaming', 'echoroukonline'); ?></span>
                                        </div>
                                        <div class="hero-floating-frame">
                                            <?php if ($floating_embed) : ?>
                                                <?php echo $floating_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                                                ?>
                                            <?php else : ?>
                                                <a href="<?php echo esc_url($floating_url); ?>" target="_blank"
                                                    rel="noopener noreferrer"><?php echo esc_html($floating_url); ?></a>
                                            <?php endif; ?>
                                        </div>
                                    </aside>
                                <?php endif; ?>
                            </div>

                            <div class="hero-lead-box-wrap">
                                <div class="hero-lead-text-box">
                                    <h1 class="headline"><a
                                            href="<?php echo esc_url(get_permalink($hero_main)); ?>"><?php echo esc_html(get_the_title($hero_main)); ?></a>
                                    </h1>
                                    <div class="hero-meta-line"><?php echo esc_html(get_the_date('', $hero_main)); ?></div>
                                    <p class="summary mb-0"><?php echo esc_html($get_excerpt($hero_main, 28)); ?></p>
                                </div>
                                <div class="hero-lead-icons-box"
                                    aria-label="<?php esc_attr_e('Article actions', 'echoroukonline'); ?>">
                                    <a href="<?php echo esc_url(get_permalink($hero_main)); ?>" class="hero-lead-icon"><img
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/share-08-stroke-rounded.svg'); ?>"
                                            alt=""></a>
                                    <a href="<?php echo esc_url(get_permalink($hero_main)); ?>" class="hero-lead-icon"><img
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/menu-01-stroke-rounded.svg'); ?>"
                                            alt=""></a>
                                    <a href="<?php echo esc_url(get_permalink($hero_main)); ?>"
                                        class="hero-lead-icon is-active"><img
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/headphones-stroke-rounded.svg'); ?>"
                                            alt=""></a>
                                    <a href="<?php echo esc_url(get_permalink($hero_main)); ?>" class="hero-lead-icon"><img
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/bookmark-02-stroke-rounded.svg'); ?>"
                                            alt=""></a>
                                </div>
                            </div>
                        </article>
                    <?php endif; ?>
                </section>

                <aside class="col-lg-4 order-2 order-lg-1 hero-col-left">
                    <div class="hero-live hero-col-card">
                        <div class="hero-live-title">
                            <span><?php esc_html_e('Live Coverage', 'echoroukonline'); ?></span><img
                                src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/arrow-left-01-stroke-rounded.svg'); ?>"
                                alt="">
                        </div>
                        <ul class="hero-live-timeline">
                            <?php if (! empty($live_timeline_items)) : ?>
                                <?php foreach ($live_timeline_items as $timeline_item) : ?>
                                    <li>
                                        <time class="hero-live-time"
                                            datetime="<?php echo esc_attr($timeline_item['datetime']); ?>"><?php echo esc_html($timeline_item['time']); ?></time>
                                        <span><a
                                                href="<?php echo esc_url($timeline_item['url']); ?>"><?php echo esc_html($timeline_item['title']); ?></a></span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </aside>
            </div>
        </section>

        <div class="ad-box my-4"><?php esc_html_e('Ads', 'echoroukonline'); ?></div>

        <?php if ($world_main) : ?>
            <hr class="section-divider my-4">
            <section class="world-spotlight grid-border">
                <div class="row g-4 align-items-stretch world-spotlight-grid">
                    <aside class="col-lg-3 world-side">
                        <?php foreach ($world_left as $world_post) : ?>
                            <article class="world-mini-card">
                                <div class="world-mini-media">
                                    <a
                                        href="<?php echo esc_url(get_permalink($world_post)); ?>"><?php echo echorouk_post_image_html($world_post->ID, 'medium'); ?></a>
                                    <span
                                        class="world-mini-tag"><?php echo esc_html(echorouk_get_primary_category($world_post->ID) ? echorouk_get_primary_category($world_post->ID)->name : __('العالم', 'echoroukonline')); ?></span>
                                </div>
                                <div class="world-mini-date"><?php echo esc_html(get_the_date('Y/m/d', $world_post)); ?></div>
                                <h3><a
                                        href="<?php echo esc_url(get_permalink($world_post)); ?>"><?php echo esc_html(get_the_title($world_post)); ?></a>
                                </h3>
                            </article>
                        <?php endforeach; ?>
                    </aside>

                    <section class="col-lg-6">
                        <article class="world-feature-card">
                            <div class="world-feature-media">
                                <a
                                    href="<?php echo esc_url(get_permalink($world_main)); ?>"><?php echo echorouk_post_image_html($world_main->ID, 'large'); ?></a>
                                <span
                                    class="world-feature-tag"><?php echo esc_html(echorouk_get_primary_category($world_main->ID) ? echorouk_get_primary_category($world_main->ID)->name : __('العالم', 'echoroukonline')); ?></span>
                            </div>
                            <div class="world-feature-body">
                                <div class="world-feature-date"><?php echo esc_html(get_the_date('Y/m/d', $world_main)); ?>
                                </div>
                                <h3><a
                                        href="<?php echo esc_url(get_permalink($world_main)); ?>"><?php echo esc_html(get_the_title($world_main)); ?></a>
                                </h3>
                                <p><?php echo esc_html($get_excerpt($world_main, 30)); ?></p>
                            </div>
                        </article>
                    </section>

                    <aside class="col-lg-3 world-side">
                        <?php foreach ($world_right as $world_post) : ?>
                            <article class="world-mini-card">
                                <div class="world-mini-media">
                                    <a
                                        href="<?php echo esc_url(get_permalink($world_post)); ?>"><?php echo echorouk_post_image_html($world_post->ID, 'medium'); ?></a>
                                    <span
                                        class="world-mini-tag"><?php echo esc_html(echorouk_get_primary_category($world_post->ID) ? echorouk_get_primary_category($world_post->ID)->name : __('العالم', 'echoroukonline')); ?></span>
                                </div>
                                <div class="world-mini-date"><?php echo esc_html(get_the_date('Y/m/d', $world_post)); ?></div>
                                <h3><a
                                        href="<?php echo esc_url(get_permalink($world_post)); ?>"><?php echo esc_html(get_the_title($world_post)); ?></a>
                                </h3>
                            </article>
                        <?php endforeach; ?>
                    </aside>
                </div>
            </section>
        <?php endif; ?>

        <?php if (! empty($most_read_posts)) : ?>
            <hr class="section-divider my-4">
            <section class="grid-border">
                <header class="most-read-header">
                    <h5 class="section-title"><span><?php esc_html_e('Most read', 'echoroukonline'); ?></span></h5>
                    <h5 class="most-read-tabs">
                        <ul class="most-read-time-filters" role="tablist"
                            aria-label="<?php esc_attr_e('Most read filters', 'echoroukonline'); ?>">
                            <li role="presentation"><button type="button" class="most-read-time-filter"
                                    data-time-range="day" role="tab" aria-selected="false"
                                    aria-pressed="false"><?php esc_html_e('Today', 'echoroukonline'); ?></button></li>
                            <li role="presentation"><button type="button" class="most-read-time-filter"
                                    data-time-range="week" role="tab" aria-selected="false"
                                    aria-pressed="false"><?php esc_html_e('This week', 'echoroukonline'); ?></button></li>
                            <li role="presentation"><button type="button" class="most-read-time-filter is-active"
                                    data-time-range="month" role="tab" aria-selected="true"
                                    aria-pressed="true"><?php esc_html_e('This month', 'echoroukonline'); ?></button></li>
                        </ul>
                    </h5>
                </header>
                <div class="row g-3 most-read-grid">
                    <?php foreach ($most_read_posts as $most_read_post) : ?>
                        <div class="col-6 col-md-3">
                            <article class="news-card">
                                <a
                                    href="<?php echo esc_url(get_permalink($most_read_post)); ?>"><?php echo echorouk_post_image_html($most_read_post->ID, 'medium'); ?></a>
                                <div class="most-read-mini-date"><?php echo esc_html(get_the_date('Y/m/d', $most_read_post)); ?>
                                </div>
                                <h3 class="small-headline mt-2"><a
                                        href="<?php echo esc_url(get_permalink($most_read_post)); ?>"><?php echo esc_html(get_the_title($most_read_post)); ?></a>
                                </h3>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (! empty($video_posts)) : ?>
            <?php
            $video_side_feature = $video_posts[0];
            $video_side_list    = array_slice($video_posts, 1, 3);
            $video_used_ids     = array($video_side_feature->ID);
            foreach ($video_side_list as $video_side_item) {
                $video_used_ids[] = $video_side_item->ID;
            }

            $video_remaining = array();
            foreach ($video_posts as $video_post_item) {
                if (in_array($video_post_item->ID, $video_used_ids, true)) {
                    continue;
                }
                $video_remaining[] = $video_post_item;
            }

            $video_main_feature = ! empty($video_remaining) ? array_shift($video_remaining) : $video_side_feature;
            $video_bottom_cards = array_slice($video_remaining, 0, 2);

            if (count($video_bottom_cards) < 2) {
                $video_excluded_ids = array($video_side_feature->ID, $video_main_feature->ID);
                foreach ($video_side_list as $video_side_item) {
                    $video_excluded_ids[] = $video_side_item->ID;
                }
                foreach ($video_bottom_cards as $video_bottom_item) {
                    $video_excluded_ids[] = $video_bottom_item->ID;
                }

                $video_bottom_fill = $filter_posts(
                    get_posts(
                        array(
                            'post_type'           => array('video', 'post'),
                            'post_status'         => 'publish',
                            'posts_per_page'      => 2 - count($video_bottom_cards),
                            'post__not_in'        => array_values(array_unique(array_map('absint', $video_excluded_ids))),
                            'orderby'             => 'date',
                            'order'               => 'DESC',
                            'ignore_sticky_posts' => true,
                        )
                    )
                );

                if (! empty($video_bottom_fill)) {
                    $video_bottom_cards = array_values(array_merge($video_bottom_cards, $video_bottom_fill));
                }
            }
            ?>
            <hr class="section-divider my-4">
            <section class="video-showcase grid-border">
                <div class="video-showcase-grid">
                    <aside class="col-lg-3 video-showcase-side">
                        <div class="video-side-ad"><?php esc_html_e('Ads', 'echoroukonline'); ?><br>300/250</div>

                        <div class="video-side-most">
                            <div class="video-side-title-wrap">
                                <h3 class="video-side-title"><?php esc_html_e('Most viewed', 'echoroukonline'); ?></h3>
                                <img src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/arrow-left-double-stroke-rounded.svg'); ?>"
                                    alt="">
                            </div>

                            <article class="video-side-feature">
                                <a class="video-side-thumb"
                                    href="<?php echo esc_url(get_permalink($video_side_feature)); ?>">
                                    <?php echo echorouk_post_image_html($video_side_feature->ID, 'medium'); ?>
                                    <span class="video-thumb-play" aria-hidden="true"><img
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/play-stroke-rounded-2.svg'); ?>"
                                            alt=""></span>
                                </a>
                                <div class="video-side-date">
                                    <?php echo esc_html(get_the_date('Y/m/d', $video_side_feature)); ?></div>
                                <h4><a
                                        href="<?php echo esc_url(get_permalink($video_side_feature)); ?>"><?php echo esc_html(get_the_title($video_side_feature)); ?></a>
                                </h4>
                            </article>

                            <div class="video-side-list">
                                <?php foreach ($video_side_list as $video_side_item) : ?>
                                    <article class="video-side-item">
                                        <a class="video-side-thumb"
                                            href="<?php echo esc_url(get_permalink($video_side_item)); ?>">
                                            <?php echo echorouk_post_image_html($video_side_item->ID, 'thumbnail', 'video-side-thumb-img'); ?>
                                            <span class="video-thumb-play" aria-hidden="true"><img
                                                    src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/play-stroke-rounded-2.svg'); ?>"
                                                    alt=""></span>
                                        </a>
                                        <div>
                                            <h5><a
                                                    href="<?php echo esc_url(get_permalink($video_side_item)); ?>"><?php echo esc_html(get_the_title($video_side_item)); ?></a>
                                            </h5>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </aside>

                    <section class="col-lg-9 video-showcase-main">
                        <header class="video-main-header">
                            <div class="video-main-logo-wrap">
                                <div class="video-main-kicker"><?php esc_html_e('Videos', 'echoroukonline'); ?></div>
                                <div class="video-main-logo" id="echorouk-logo-white"></div>
                            </div>
                            <a href="<?php echo esc_url(get_post_type_archive_link('video') ? get_post_type_archive_link('video') : home_url('/')); ?>"
                                class="video-main-all"><?php esc_html_e('All videos', 'echoroukonline'); ?><span
                                    aria-hidden="true"><img style="transform: matrix(-1, 0, 0, -1, 0, 0);"
                                        src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/play-stroke-rounded.svg'); ?>"
                                        alt=""></span></a>
                        </header>

                        <article class="video-main-feature">
                            <a
                                href="<?php echo esc_url(get_permalink($video_main_feature)); ?>"><?php echo echorouk_post_image_html($video_main_feature->ID, 'large'); ?></a>
                            <div class="video-main-overlay">
                                <div class="video-main-feature-date">
                                    <?php echo esc_html(get_the_date('d/m/Y', $video_main_feature)); ?></div>
                                <div class="video-main-feature-title">
                                    <img src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/play-circle-stroke-rounded.svg'); ?>"
                                        alt="">
                                    <h3><a
                                            href="<?php echo esc_url(get_permalink($video_main_feature)); ?>"><?php echo esc_html(get_the_title($video_main_feature)); ?></a>
                                    </h3>
                                </div>
                            </div>
                        </article>

                        <?php if (! empty($video_bottom_cards)) : ?>
                            <div class="video-main-bottom">
                                <?php foreach ($video_bottom_cards as $video_bottom_post) : ?>
                                    <article class="video-bottom-card">
                                        <a
                                            href="<?php echo esc_url(get_permalink($video_bottom_post)); ?>"><?php echo echorouk_post_image_html($video_bottom_post->ID, 'medium_large'); ?></a>
                                        <div class="video-bottom-date">
                                            <?php echo esc_html(get_the_date('d/m/Y', $video_bottom_post)); ?></div>
                                        <h4><a
                                                href="<?php echo esc_url(get_permalink($video_bottom_post)); ?>"><?php echo esc_html(get_the_title($video_bottom_post)); ?></a>
                                        </h4>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($sport_main) : ?>
            <hr class="section-divider my-4">
            <section class="grid-border sports-section">
                <div class="video-sports-logo-wrap">
                    <div class="video-sports-logo" id="echorouk-sports-logo-dark"></div>
                </div>
                <div class="row g-4 align-items-center sports-main-grid">
                    <div class="col-lg-4 sports-main-article">
                        <h3><a
                                href="<?php echo esc_url(get_permalink($sport_main)); ?>"><?php echo esc_html(get_the_title($sport_main)); ?></a>
                        </h3>
                        <p class="summary"><?php echo esc_html($get_excerpt($sport_main, 20)); ?></p>
                    </div>
                    <div class="col-lg-8 sports-main-media"><a
                            href="<?php echo esc_url(get_permalink($sport_main)); ?>"><?php echo echorouk_post_image_html($sport_main->ID, 'large', 'img-fluid'); ?></a>
                    </div>
                </div>
                <?php if (! empty($sport_cards)) : ?>
                    <div class="row g-3 mt-2 sports-sub-grid">
                        <?php foreach ($sport_cards as $sport_card) : ?>
                            <div class="col-6 col-md-3">
                                <article class="news-card">
                                    <a
                                        href="<?php echo esc_url(get_permalink($sport_card)); ?>"><?php echo echorouk_post_image_html($sport_card->ID, 'medium'); ?></a>
                                    <div class="mini-date"><?php echo esc_html(get_the_date('Y/m/d', $sport_card)); ?></div>
                                    <h3 class="small-headline mt-2"><a
                                            href="<?php echo esc_url(get_permalink($sport_card)); ?>"><?php echo esc_html(get_the_title($sport_card)); ?></a>
                                    </h3>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($economy_main) : ?>
            <hr class="section-divider my-4">
            <section class="grid-border economy-section">
                <h5 class="section-title"><span><?php esc_html_e('Economy', 'echoroukonline'); ?></span></h5>
                <div class="row g-4 align-items-center economy-main-grid">
                    <div class="col-lg-4 economy-main-article">
                        <h3 class="headline"><a
                                href="<?php echo esc_url(get_permalink($economy_main)); ?>"><?php echo esc_html(get_the_title($economy_main)); ?></a>
                        </h3>
                        <p class="summary"><?php echo esc_html($get_excerpt($economy_main, 20)); ?></p>
                    </div>
                    <div class="col-lg-8 economy-main-media"><a
                            href="<?php echo esc_url(get_permalink($economy_main)); ?>"><?php echo echorouk_post_image_html($economy_main->ID, 'large', 'img-fluid'); ?></a>
                    </div>
                </div>
                <?php if (! empty($economy_cards)) : ?>
                    <div class="row g-3 mt-2 economy-sub-grid">
                        <?php foreach ($economy_cards as $economy_card) : ?>
                            <div class="col-6 col-md-3">
                                <article class="news-card">
                                    <a
                                        href="<?php echo esc_url(get_permalink($economy_card)); ?>"><?php echo echorouk_post_image_html($economy_card->ID, 'medium'); ?></a>
                                    <div class="mini-date"><?php echo esc_html(get_the_date('Y/m/d', $economy_card)); ?></div>
                                    <h3 class="small-headline mt-2"><a
                                            href="<?php echo esc_url(get_permalink($economy_card)); ?>"><?php echo esc_html(get_the_title($economy_card)); ?></a>
                                    </h3>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (! empty($opinion_posts)) : ?>
            <hr class="section-divider my-4">
            <section class="blue-panel mb-5 opinion-panel">
                <div class="opinion-main-logo-wrap">
                    <div class="opinion-main-kicker"><?php esc_html_e('Opinion', 'echoroukonline'); ?></div>
                    <div class="opinion-main-logo" id="echorouk-logo-white"></div>
                </div>
                <div class="row g-0 opinion-grid">
                    <?php foreach ($opinion_posts as $opinion_post) : ?>
                        <?php
                        $author_name = get_the_author_meta('display_name', $opinion_post->post_author);
                        $author_img  = get_avatar_url($opinion_post->post_author, array('size' => 96, 'default' => 'mystery'));
                        ?>
                        <div class="col-lg-3 col-md-6 col-12">
                            <article class="opinion-card">
                                <a
                                    href="<?php echo esc_url(get_permalink($opinion_post)); ?>"><?php echo echorouk_post_image_html($opinion_post->ID, 'medium_large', 'opinion-card-thumb'); ?></a>
                                <div class="opinion-card-meta">
                                    <div class="opinion-card-author">
                                        <div class="author-name-date">
                                            <span class="opinion-card-author-name"><?php echo esc_html($author_name); ?></span>
                                            <div class="opinion-card-date">
                                                <?php echo esc_html(get_the_date('Y/m/d', $opinion_post)); ?></div>
                                        </div>
                                        <img class="avatar" src="<?php echo esc_url($author_img); ?>"
                                            alt="<?php echo esc_attr($author_name); ?>" loading="lazy" decoding="async">
                                    </div>
                                </div>
                                <h3 class="opinion-card-title"><a
                                        href="<?php echo esc_url(get_permalink($opinion_post)); ?>"><?php echo esc_html(get_the_title($opinion_post)); ?></a>
                                </h3>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($diplomacy_main) : ?>
            <hr class="section-divider my-4">
            <section class="diplomacy-spotlight grid-border">
                <div class="row g-0 align-items-start diplomacy-spotlight-top">
                    <div class="col-lg-3 col-12 diplomacy-top-col diplomacy-top-main">
                        <article class="diplomacy-main-story">
                            <h3><a
                                    href="<?php echo esc_url(get_permalink($diplomacy_main)); ?>"><?php echo esc_html(get_the_title($diplomacy_main)); ?></a>
                            </h3>
                            <div class="diplomacy-main-date"><?php echo esc_html(get_the_date('Y/m/d', $diplomacy_main)); ?>
                            </div>
                        </article>
                    </div>
                    <div class="col-lg-6 col-12 diplomacy-top-col diplomacy-top-feature">
                        <?php if ($diplomacy_feature) : ?>
                            <article class="diplomacy-feature-media">
                                <a
                                    href="<?php echo esc_url(get_permalink($diplomacy_feature)); ?>"><?php echo echorouk_post_image_html($diplomacy_feature->ID, 'large'); ?></a>
                            </article>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-3 col-12 diplomacy-top-col diplomacy-top-side">
                        <?php if (! empty($diplomacy_side)) : ?>
                            <aside class="diplomacy-side-list">
                                <?php foreach ($diplomacy_side as $diplomacy_side_post) : ?>
                                    <article class="diplomacy-side-item">
                                        <div class="diplomacy-side-date">
                                            <?php echo esc_html(get_the_date('Y/m/d', $diplomacy_side_post)); ?></div>
                                        <h4><a
                                                href="<?php echo esc_url(get_permalink($diplomacy_side_post)); ?>"><?php echo esc_html(get_the_title($diplomacy_side_post)); ?></a>
                                        </h4>
                                    </article>
                                <?php endforeach; ?>
                            </aside>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (! empty($diplomacy_bottom)) : ?>
                    <div class="row g-0 diplomacy-spotlight-bottom">
                        <?php foreach ($diplomacy_bottom as $diplomacy_bottom_post) : ?>
                            <div class="col-lg-4 col-12 diplomacy-bottom-col">
                                <article class="diplomacy-bottom-item">
                                    <div class="diplomacy-bottom-date">
                                        <?php echo esc_html(get_the_date('Y/m/d', $diplomacy_bottom_post)); ?></div>
                                    <h4><a
                                            href="<?php echo esc_url(get_permalink($diplomacy_bottom_post)); ?>"><?php echo esc_html(get_the_title($diplomacy_bottom_post)); ?></a>
                                    </h4>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($podcast_feature) : ?>
            <hr class="section-divider my-4">
            <section class="podcast-section mb-5">
                <header class="podcast-section-head">
                    <h5 class="podcast-section-title"><?php esc_html_e('Podcast', 'echoroukonline'); ?></h5>
                </header>
                <div class="row g-3 podcast-grid align-items-stretch">
                    <div class="col-lg-3 col-12">
                        <aside class="podcast-follow-card">
                            <p class="podcast-follow-title">
                                <?php esc_html_e('Follow Echourouk podcasts on our platforms', 'echoroukonline'); ?></p>
                            <div class="podcast-follow-platforms"
                                aria-label="<?php esc_attr_e('Podcast platforms', 'echoroukonline'); ?>">
                                <?php if ($podcast_primary_url) : ?>
                                    <a href="<?php echo esc_url($podcast_primary_url); ?>" target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="<?php esc_attr_e('Primary podcast platform', 'echoroukonline'); ?>"><img
                                            class="podcast-follow-platforms-icon"
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/music-note-04-stroke-rounded.svg'); ?>"
                                            alt=""></a>
                                <?php endif; ?>
                                <?php if ($podcast_secondary_url) : ?>
                                    <a href="<?php echo esc_url($podcast_secondary_url); ?>" target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="<?php esc_attr_e('Secondary podcast platform', 'echoroukonline'); ?>"><img
                                            class="podcast-follow-platforms-icon"
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/podcast-stroke-rounded-2.svg'); ?>"
                                            alt=""></a>
                                <?php endif; ?>
                                <?php if ($podcast_soundcloud_url) : ?>
                                    <a href="<?php echo esc_url($podcast_soundcloud_url); ?>" target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="<?php esc_attr_e('SoundCloud', 'echoroukonline'); ?>"><img
                                            class="podcast-follow-platforms-icon"
                                            src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/soundcloud-stroke-rounded.svg'); ?>"
                                            alt=""></a>
                                <?php endif; ?>
                            </div>
                            <div class="podcast-box-link">
                                <a href="<?php echo esc_url($podcast_archive_url); ?>"><?php esc_html_e('More', 'echoroukonline'); ?>
                                    <img src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/podcast-stroke-rounded-2.svg'); ?>"
                                        alt=""></a>
                            </div>
                        </aside>
                    </div>

                    <div class="col-lg-4 col-12">
                        <section class="podcast-center">
                            <div class="podcast-list">
                                <?php foreach ($podcast_list as $podcast_item) : ?>
                                    <article class="podcast-list-item">
                                        <a href="<?php echo esc_url(get_permalink($podcast_item)); ?>"
                                            class="podcast-list-thumb">
                                            <?php echo echorouk_post_image_html($podcast_item->ID, 'medium'); ?>
                                            <span class="podcast-icon"><img
                                                    src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/podcast-stroke-rounded-2.svg'); ?>"
                                                    alt=""></span>
                                        </a>
                                        <div class="podcast-list-copy">
                                            <time
                                                datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $podcast_item)); ?>"><?php echo esc_html(get_the_date('d/m/Y', $podcast_item)); ?></time>
                                            <h3><a
                                                    href="<?php echo esc_url(get_permalink($podcast_item)); ?>"><?php echo esc_html(get_the_title($podcast_item)); ?></a>
                                            </h3>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-5 col-12">
                        <article class="podcast-feature">
                            <a
                                href="<?php echo esc_url(get_permalink($podcast_feature)); ?>"><?php echo echorouk_post_image_html($podcast_feature->ID, 'large'); ?></a>
                            <span class="podcast-icon"><img
                                    src="<?php echo esc_url(ECHOROUK_THEME_URI . '/assets/icons/podcast-stroke-rounded-2.svg'); ?>"
                                    alt=""></span>
                            <div class="podcast-feature-body">
                                <time
                                    datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $podcast_feature)); ?>"><?php echo esc_html(get_the_date('d/m/Y', $podcast_feature)); ?></time>
                                <h3><a
                                        href="<?php echo esc_url(get_permalink($podcast_feature)); ?>"><?php echo esc_html(get_the_title($podcast_feature)); ?></a>
                                </h3>
                            </div>
                        </article>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <hr class="section-divider my-4">
        <section class="newsletter mb-5">
            <div class="row align-items-center g-3">
                <div class="col-lg-5">
                    <h5 class="headline mb-1"><?php esc_html_e('Newsletter subscription', 'echoroukonline'); ?></h5>
                    <p class="summary mb-0">
                        <?php esc_html_e('Get the top stories and analysis directly in your inbox.', 'echoroukonline'); ?>
                    </p>
                    <?php if (is_array($newsletter_feedback) && ! empty($newsletter_feedback['message'])) : ?>
                        <p class="summary mb-0 newsletter-feedback newsletter-feedback--<?php echo esc_attr($newsletter_feedback['type']); ?>"
                            role="status"><?php echo esc_html($newsletter_feedback['message']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-lg-7">
                    <form class="input-group" action="<?php echo esc_url($newsletter_action); ?>" method="post">
                        <?php wp_nonce_field('echorouk_newsletter_signup', 'echorouk_newsletter_nonce'); ?>
                        <?php if ($newsletter_internal) : ?>
                            <input type="hidden" name="action" value="echorouk_newsletter_subscribe">
                        <?php endif; ?>
                        <input type="email" class="form-control" name="email"
                            placeholder="<?php esc_attr_e('Email address', 'echoroukonline'); ?>" required>
                        <button class="btn btn-warning text-white"
                            type="submit"><?php esc_html_e('Subscribe', 'echoroukonline'); ?></button>
                    </form>
                </div>
            </div>
        </section>

        <?php if ($jawaher_main) : ?>
            <hr class="section-divider my-4">
            <section class="grid-border jawaher-section">
                <div class="jawaher-logo-wrap">
                    <div class="jawaher-logo" id="echorouk-jawahir-logo-dark"></div>
                </div>
                <div class="row g-4 align-items-center jawaher-main-grid">
                    <div class="col-lg-4 jawaher-main-article">
                        <h3><a
                                href="<?php echo esc_url(get_permalink($jawaher_main)); ?>"><?php echo esc_html(get_the_title($jawaher_main)); ?></a>
                        </h3>
                        <p class="summary"><?php echo esc_html($get_excerpt($jawaher_main, 20)); ?></p>
                    </div>
                    <div class="col-lg-8 jawaher-main-media"><a
                            href="<?php echo esc_url(get_permalink($jawaher_main)); ?>"><?php echo echorouk_post_image_html($jawaher_main->ID, 'large', 'img-fluid'); ?></a>
                    </div>
                </div>
                <?php if (! empty($jawaher_cards)) : ?>
                    <div class="row g-3 mt-2 jawaher-sub-grid">
                        <?php foreach ($jawaher_cards as $jawaher_card) : ?>
                            <div class="col-6 col-md-3">
                                <article class="news-card">
                                    <a
                                        href="<?php echo esc_url(get_permalink($jawaher_card)); ?>"><?php echo echorouk_post_image_html($jawaher_card->ID, 'medium'); ?></a>
                                    <div class="jawaher-mini-date"><?php echo esc_html(get_the_date('Y/m/d', $jawaher_card)); ?>
                                    </div>
                                    <h3 class="small-headline mt-2"><a
                                            href="<?php echo esc_url(get_permalink($jawaher_card)); ?>"><?php echo esc_html(get_the_title($jawaher_card)); ?></a>
                                    </h3>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (! empty($french_posts) || ! empty($english_posts)) : ?>
            <hr class="section-divider my-4">
            <section class="other-languages-section grid-border mb-5">
                <div class="row g-0">
                    <div class="col-lg-6 col-md-6 col-12 other-lang-col">
                        <h3 class="other-lang-title">Français</h3>
                        <?php foreach ($french_posts as $latest_post) : ?>
                            <article class="other-lang-item">
                                <a
                                    href="<?php echo esc_url(get_permalink($latest_post)); ?>"><?php echo echorouk_post_image_html($latest_post->ID, 'thumbnail'); ?></a>
                                <div class="other-lang-copy">
                                    <time
                                        datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $latest_post)); ?>"><?php echo esc_html(get_the_date('d/m/Y', $latest_post)); ?></time>
                                    <h4><a
                                            href="<?php echo esc_url(get_permalink($latest_post)); ?>"><?php echo esc_html(get_the_title($latest_post)); ?></a>
                                    </h4>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-lg-6 col-md-6 col-12 other-lang-col">
                        <h3 class="other-lang-title">English</h3>
                        <?php foreach ($english_posts as $latest_post) : ?>
                            <article class="other-lang-item">
                                <a
                                    href="<?php echo esc_url(get_permalink($latest_post)); ?>"><?php echo echorouk_post_image_html($latest_post->ID, 'thumbnail'); ?></a>
                                <div class="other-lang-copy">
                                    <time
                                        datetime="<?php echo esc_attr(get_the_date(DATE_W3C, $latest_post)); ?>"><?php echo esc_html(get_the_date('d/m/Y', $latest_post)); ?></time>
                                    <h4><a
                                            href="<?php echo esc_url(get_permalink($latest_post)); ?>"><?php echo esc_html(get_the_title($latest_post)); ?></a>
                                    </h4>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <hr class="section-divider my-4">
        <div class="ad-box"><?php esc_html_e('Ads', 'echoroukonline'); ?></div>
    </div>
</main>

<script>
    document.addEventListener('click', function(event) {
        var closeButton = event.target.closest('.hero-floating-close');
        if (!closeButton) {
            var mostReadFilter = event.target.closest('.most-read-time-filter');
            if (!mostReadFilter) {
                return;
            }

            var filterGroup = mostReadFilter.closest('.most-read-time-filters');
            if (!filterGroup) {
                return;
            }

            var filters = filterGroup.querySelectorAll('.most-read-time-filter');
            filters.forEach(function(filter) {
                filter.classList.remove('is-active');
                filter.setAttribute('aria-selected', 'false');
                filter.setAttribute('aria-pressed', 'false');
            });

            mostReadFilter.classList.add('is-active');
            mostReadFilter.setAttribute('aria-selected', 'true');
            mostReadFilter.setAttribute('aria-pressed', 'true');
            return;
        }

        var floatingVideo = closeButton.closest('.hero-floating-video');
        if (floatingVideo) {
            floatingVideo.style.display = 'none';
        }
    });
</script>
