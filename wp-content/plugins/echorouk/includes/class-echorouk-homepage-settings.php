<?php

if (! defined('ABSPATH')) {
    exit;
}

class Echorouk_Homepage_Settings
{
    const OPTION_KEY = 'echorouk_homepage_config_v1';

    /**
     * Initialize defaults on activation.
     *
     * @return void
     */
    public static function activate()
    {
        $instance = new self();
        $config = get_option(self::OPTION_KEY);

        if (! is_array($config)) {
            add_option(self::OPTION_KEY, $instance->get_default_config(), '', false);
            return;
        }

        update_option(self::OPTION_KEY, $instance->normalize_config($config), false);
    }

    /**
     * Registry for homepage sections. Extendable via filter.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_registered_sections()
    {
        $sections = [
            [
                'id'            => 'news_ticker',
                'label'         => __('News Ticker', 'echorouk-homepage'),
                'supports_posts'=> true,
                'default_source'=> 'latest',
                'default_limit' => 10,
                'default_meta'  => [
                    'show_latest'   => true,
                    'show_breaking' => true,
                ],
            ],
            [
                'id'            => 'hero',
                'label'         => __('Hero', 'echorouk-homepage'),
                'supports_posts'=> false,
                'default_source'=> 'manual',
                'default_limit' => 0,
                'default_meta'  => [
                    'main_post_id'           => 0,
                    'live_coverage_enabled'  => false,
                    'live_post_id'           => 0,
                    'side_post_ids'          => [],
                    'left_column_post_ids'   => [],
                    'fallback_post_ids'      => [],
                ],
            ],
            [
                'id'            => 'world',
                'label'         => __('World', 'echorouk-homepage'),
                'supports_posts'=> false,
                'default_source'=> 'manual',
                'default_limit' => 0,
                'default_meta'  => [
                    'main_post_id'       => 0,
                    'secondary_post_ids' => [],
                ],
            ],
            [
                'id'            => 'video',
                'label'         => __('Video', 'echorouk-homepage'),
                'supports_posts'=> true,
                'default_source'=> 'manual',
                'default_limit' => 5,
                'default_meta'  => [
                    'video_url' => '',
                ],
            ],
            [
                'id'            => 'sport',
                'label'         => __('Sport', 'echorouk-homepage'),
                'supports_posts'=> true,
                'default_source'=> 'latest',
                'default_limit' => 6,
                'default_meta'  => [],
            ],
            [
                'id'            => 'economy',
                'label'         => __('Economy', 'echorouk-homepage'),
                'supports_posts'=> true,
                'default_source'=> 'latest',
                'default_limit' => 6,
                'default_meta'  => [],
            ],
            [
                'id'            => 'opinion',
                'label'         => __('Opinion', 'echorouk-homepage'),
                'supports_posts'=> true,
                'default_source'=> 'latest',
                'default_limit' => 6,
                'default_meta'  => [],
            ],
            [
                'id'            => 'last',
                'label'         => __('Last Section', 'echorouk-homepage'),
                'supports_posts'=> true,
                'default_source'=> 'latest',
                'default_limit' => 6,
                'default_meta'  => [],
            ],
            [
                'id'            => 'jawaher',
                'label'         => __('Jawaher', 'echorouk-homepage'),
                'supports_posts'=> true,
                'default_source'=> 'latest',
                'default_limit' => 6,
                'default_meta'  => [],
            ],
            [
                'id'            => 'floating_video',
                'label'         => __('Floating Video', 'echorouk-homepage'),
                'supports_posts'=> false,
                'default_source'=> 'manual',
                'default_limit' => 0,
                'default_meta'  => [
                    'video_url' => '',
                    'autoplay'  => false,
                ],
            ],
        ];

        $sections = apply_filters('echorouk_homepage_sections_registry', $sections);

        return is_array($sections) ? array_values($sections) : [];
    }

    /**
     * Default plugin configuration.
     *
     * @return array<string, mixed>
     */
    public function get_default_config()
    {
        $sections = [];

        foreach ($this->get_registered_sections() as $section) {
            if (empty($section['id'])) {
                continue;
            }

            $sections[] = [
                'id'       => (string) $section['id'],
                'label'    => isset($section['label']) ? (string) $section['label'] : (string) $section['id'],
                'enabled'  => true,
                'source'   => isset($section['default_source']) ? (string) $section['default_source'] : 'manual',
                'limit'    => isset($section['default_limit']) ? absint($section['default_limit']) : 0,
                'post_ids' => [],
                'meta'     => isset($section['default_meta']) && is_array($section['default_meta']) ? $section['default_meta'] : [],
            ];
        }

        return [
            'version'    => 1,
            'updated_at' => current_time('mysql'),
            'sections'   => $sections,
        ];
    }

    /**
     * Get normalized configuration.
     *
     * @return array<string, mixed>
     */
    public function get_config()
    {
        $config = get_option(self::OPTION_KEY, []);

        if (! is_array($config)) {
            $config = [];
        }

        return $this->normalize_config($config);
    }

    /**
     * Persist normalized configuration.
     *
     * @param mixed $input Raw config.
     * @return array<string, mixed>
     */
    public function update_config($input)
    {
        $normalized = $this->normalize_config($input);
        update_option(self::OPTION_KEY, $normalized, false);

        return $normalized;
    }

    /**
     * Reset to defaults.
     *
     * @return array<string, mixed>
     */
    public function reset_config()
    {
        $defaults = $this->get_default_config();
        update_option(self::OPTION_KEY, $defaults, false);

        return $defaults;
    }

    /**
     * Normalize potentially malformed config.
     *
     * @param mixed $input Input payload.
     * @return array<string, mixed>
     */
    public function normalize_config($input)
    {
        $defaults = $this->get_default_config();

        if (! is_array($input)) {
            return $defaults;
        }

        $sections_input = isset($input['sections']) && is_array($input['sections']) ? $input['sections'] : [];
        $sections_by_id = [];
        $requested_order = [];

        foreach ($sections_input as $section_row) {
            if (! is_array($section_row) || empty($section_row['id'])) {
                continue;
            }

            $id = sanitize_key($section_row['id']);
            if ($id === '') {
                continue;
            }

            $sections_by_id[$id] = $section_row;
            $requested_order[] = $id;
        }

        $registered = $this->get_registered_sections();
        $registry_by_id = [];

        foreach ($registered as $registry_item) {
            if (empty($registry_item['id'])) {
                continue;
            }
            $registry_by_id[sanitize_key($registry_item['id'])] = $registry_item;
        }

        $final_order = [];
        foreach ($requested_order as $id) {
            if (isset($registry_by_id[$id])) {
                $final_order[] = $id;
            }
        }

        foreach (array_keys($registry_by_id) as $id) {
            if (! in_array($id, $final_order, true)) {
                $final_order[] = $id;
            }
        }

        $normalized_sections = [];

        foreach ($final_order as $id) {
            $registry_item = $registry_by_id[$id];
            $default_row = [
                'id'       => $id,
                'label'    => isset($registry_item['label']) ? (string) $registry_item['label'] : $id,
                'enabled'  => true,
                'source'   => isset($registry_item['default_source']) ? (string) $registry_item['default_source'] : 'manual',
                'limit'    => isset($registry_item['default_limit']) ? absint($registry_item['default_limit']) : 0,
                'post_ids' => [],
                'meta'     => isset($registry_item['default_meta']) && is_array($registry_item['default_meta']) ? $registry_item['default_meta'] : [],
            ];

            $row = isset($sections_by_id[$id]) && is_array($sections_by_id[$id]) ? $sections_by_id[$id] : [];
            $normalized = $default_row;

            if (isset($row['label'])) {
                $normalized['label'] = sanitize_text_field((string) $row['label']);
            }

            $normalized['enabled'] = ! empty($row['enabled']);

            $source = isset($row['source']) ? sanitize_key((string) $row['source']) : $default_row['source'];
            $normalized['source'] = in_array($source, ['latest', 'manual'], true) ? $source : $default_row['source'];

            $limit = isset($row['limit']) ? absint($row['limit']) : $default_row['limit'];
            $normalized['limit'] = max(0, min(30, $limit));

            $supports_posts = ! empty($registry_item['supports_posts']);
            $normalized['post_ids'] = $supports_posts ? $this->sanitize_ids(isset($row['post_ids']) ? $row['post_ids'] : []) : [];

            $meta = isset($row['meta']) && is_array($row['meta']) ? $row['meta'] : [];
            $normalized['meta'] = $this->sanitize_section_meta($id, $meta, $default_row['meta']);

            $normalized_sections[] = $normalized;
        }

        return [
            'version'    => 1,
            'updated_at' => current_time('mysql'),
            'sections'   => $normalized_sections,
        ];
    }

    /**
     * Find one section by id.
     *
     * @param string $section_id Section id.
     * @return array<string, mixed>|null
     */
    public function get_section($section_id)
    {
        $section_id = sanitize_key($section_id);
        $config = $this->get_config();

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

    /**
     * Sanitize section meta payload.
     *
     * @param string $section_id Section id.
     * @param array<string, mixed> $meta Raw meta.
     * @param array<string, mixed> $defaults Defaults.
     * @return array<string, mixed>
     */
    protected function sanitize_section_meta($section_id, array $meta, array $defaults)
    {
        $clean = $defaults;

        foreach ($meta as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $clean[$key] = $this->sanitize_meta_value($value);
        }

        switch ($section_id) {
            case 'news_ticker':
                $clean['show_latest'] = ! empty($clean['show_latest']);
                $clean['show_breaking'] = ! empty($clean['show_breaking']);
                break;

            case 'hero':
                $clean['main_post_id'] = isset($clean['main_post_id']) ? absint($clean['main_post_id']) : 0;
                $clean['live_coverage_enabled'] = ! empty($clean['live_coverage_enabled']);
                $clean['live_post_id'] = isset($clean['live_post_id']) ? absint($clean['live_post_id']) : 0;
                $clean['side_post_ids'] = $this->sanitize_ids(isset($clean['side_post_ids']) ? $clean['side_post_ids'] : [], 5);
                $clean['left_column_post_ids'] = $this->sanitize_ids(isset($clean['left_column_post_ids']) ? $clean['left_column_post_ids'] : [], 5);
                $clean['fallback_post_ids'] = $this->sanitize_ids(isset($clean['fallback_post_ids']) ? $clean['fallback_post_ids'] : [], 5);
                break;

            case 'world':
                $clean['main_post_id'] = isset($clean['main_post_id']) ? absint($clean['main_post_id']) : 0;
                $clean['secondary_post_ids'] = $this->sanitize_ids(isset($clean['secondary_post_ids']) ? $clean['secondary_post_ids'] : [], 6);
                break;

            case 'video':
                $clean['video_url'] = isset($clean['video_url']) ? esc_url_raw((string) $clean['video_url']) : '';
                break;

            case 'floating_video':
                $clean['video_url'] = isset($clean['video_url']) ? esc_url_raw((string) $clean['video_url']) : '';
                $clean['autoplay'] = ! empty($clean['autoplay']);
                break;
        }

        return $clean;
    }

    /**
     * Recursive meta sanitizer.
     *
     * @param mixed $value Value.
     * @return mixed
     */
    protected function sanitize_meta_value($value)
    {
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $key => $item) {
                $clean_key = is_string($key) ? sanitize_key($key) : $key;
                $clean[$clean_key] = $this->sanitize_meta_value($item);
            }
            return $clean;
        }

        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if ($value === null) {
            return '';
        }

        return sanitize_text_field((string) $value);
    }

    /**
     * Sanitize post IDs.
     *
     * @param mixed $ids Input.
     * @param int $max Max count.
     * @return array<int, int>
     */
    protected function sanitize_ids($ids, $max = 30)
    {
        if (! is_array($ids)) {
            return [];
        }

        $clean = [];
        foreach ($ids as $id) {
            $abs = absint($id);
            if ($abs > 0) {
                $clean[] = $abs;
            }
        }

        $clean = array_values(array_unique($clean));

        if ($max > 0) {
            $clean = array_slice($clean, 0, $max);
        }

        return $clean;
    }
}
