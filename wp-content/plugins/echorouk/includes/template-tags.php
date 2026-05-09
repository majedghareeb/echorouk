<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('echorouk_homepage_get_config')) {
    /**
     * Get homepage editor configuration.
     *
     * @return array<string, mixed>
     */
    function echorouk_homepage_get_config()
    {
        $manager = echorouk_homepage_manager();
        $config = $manager->settings()->get_config();

        return apply_filters('echorouk_homepage_config', $config);
    }
}

if (! function_exists('echorouk_homepage_get_section')) {
    /**
     * Resolve one section payload by id.
     *
     * @param string $section_id Section ID.
     * @return array<string, mixed>|null
     */
    function echorouk_homepage_get_section($section_id)
    {
        $section_id = sanitize_key($section_id);
        $config = echorouk_homepage_get_config();

        if (empty($config['sections']) || ! is_array($config['sections'])) {
            return null;
        }

        foreach ($config['sections'] as $section) {
            if (is_array($section) && isset($section['id']) && $section['id'] === $section_id) {
                return $section;
            }
        }

        return null;
    }
}

if (! function_exists('echorouk_homepage_get_posts_for_section')) {
    /**
     * Get posts for section according to source mode.
     *
     * @param string $section_id Section id.
     * @param int $fallback_limit Fallback query limit.
     * @return array<int, WP_Post>
     */
    function echorouk_homepage_get_posts_for_section($section_id, $fallback_limit = 6)
    {
        $section = echorouk_homepage_get_section($section_id);

        if (! is_array($section) || empty($section['enabled'])) {
            return [];
        }

        $limit = isset($section['limit']) ? absint($section['limit']) : absint($fallback_limit);
        if ($limit < 1) {
            $limit = absint($fallback_limit);
        }

        if ($section_id === 'news_ticker') {
            $meta = isset($section['meta']) && is_array($section['meta']) ? $section['meta'] : [];
            $show_normal = ! isset($meta['show_latest']) || ! empty($meta['show_latest']);
            $show_breaking = ! isset($meta['show_breaking']) || ! empty($meta['show_breaking']);

            return echorouk_homepage_get_news_ticker_posts($limit, $show_normal, $show_breaking);
        }

        $source = isset($section['source']) ? (string) $section['source'] : 'manual';
        $post_ids = isset($section['post_ids']) && is_array($section['post_ids']) ? array_values(array_unique(array_map('absint', $section['post_ids']))) : [];

        if ($source === 'manual' && ! empty($post_ids)) {
            $manual = get_posts([
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'post__in'               => $post_ids,
                'orderby'                => 'post__in',
                'posts_per_page'         => min($limit, count($post_ids)),
                'ignore_sticky_posts'    => true,
                'suppress_filters'       => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]);

            return is_array($manual) ? $manual : [];
        }

        $latest = get_posts([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => max(1, $limit),
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'ignore_sticky_posts'    => true,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        return is_array($latest) ? $latest : [];
    }
}

if (! function_exists('echorouk_homepage_is_news_ticker_post')) {
    /**
     * Check if post is flagged for header ticker.
     *
     * @param int $post_id Post ID.
     * @return bool
     */
    function echorouk_homepage_is_news_ticker_post($post_id)
    {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return false;
        }

        $enabled = get_post_meta($post_id, 'echorouk_news_ticker_enabled', true);

        if ('1' === (string) $enabled || 1 === (int) $enabled || true === $enabled) {
            return true;
        }

        // Legacy fallback when older meta key is used.
        $legacy = get_post_meta($post_id, 'news_ticker', true);
        return ('1' === (string) $legacy || 1 === (int) $legacy || true === $legacy);
    }
}

if (! function_exists('echorouk_homepage_get_news_ticker_type')) {
    /**
     * Resolve ticker type: normal or breaking.
     *
     * @param int $post_id Post ID.
     * @return string
     */
    function echorouk_homepage_get_news_ticker_type($post_id)
    {
        $post_id = absint($post_id);
        if ($post_id < 1) {
            return 'normal';
        }

        $type = sanitize_key((string) get_post_meta($post_id, 'echorouk_news_ticker_type', true));
        if (in_array($type, ['normal', 'breaking'], true)) {
            return $type;
        }

        // Legacy support for previous breaking flag.
        $legacy_breaking = get_post_meta($post_id, 'breaking_news', true);
        if ('1' === (string) $legacy_breaking || 1 === (int) $legacy_breaking || true === $legacy_breaking) {
            return 'breaking';
        }

        return 'normal';
    }
}

if (! function_exists('echorouk_homepage_get_news_ticker_posts')) {
    /**
     * Get all published posts marked for news ticker.
     *
     * @param int  $limit          Max posts (-1 for all).
     * @param bool $show_normal    Include normal ticker items.
     * @param bool $show_breaking  Include breaking ticker items.
     * @return array<int, WP_Post>
     */
    function echorouk_homepage_get_news_ticker_posts($limit = -1, $show_normal = true, $show_breaking = true)
    {
        $limit = (int) $limit;
        $posts_per_page = $limit > 0 ? $limit : -1;

        if (! $show_normal && ! $show_breaking) {
            return [];
        }

        $query = get_posts([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $posts_per_page,
            'meta_query'             => [
                [
                    'key'     => 'echorouk_news_ticker_enabled',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'ignore_sticky_posts'    => true,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $posts = is_array($query) ? $query : [];

        // Backward compatibility: include legacy breaking posts if nothing is flagged.
        if (empty($posts) && $show_breaking) {
            $legacy = get_posts([
                'post_type'              => 'post',
                'post_status'            => 'publish',
                'posts_per_page'         => $posts_per_page,
                'meta_query'             => [
                    [
                        'key'     => 'breaking_news',
                        'value'   => '1',
                        'compare' => '=',
                    ],
                ],
                'orderby'                => 'date',
                'order'                  => 'DESC',
                'ignore_sticky_posts'    => true,
                'suppress_filters'       => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ]);

            $posts = is_array($legacy) ? $legacy : [];
        }

        if (empty($posts)) {
            return [];
        }

        $filtered = [];
        foreach ($posts as $post) {
            $type = echorouk_homepage_get_news_ticker_type($post->ID);
            if ('breaking' === $type && ! $show_breaking) {
                continue;
            }
            if ('breaking' !== $type && ! $show_normal) {
                continue;
            }
            $filtered[] = $post;
        }

        return $limit > 0 ? array_slice($filtered, 0, $limit) : $filtered;
    }
}
