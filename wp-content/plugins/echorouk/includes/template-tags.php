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
