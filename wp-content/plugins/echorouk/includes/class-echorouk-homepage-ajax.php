<?php

if (! defined('ABSPATH')) {
    exit;
}

class Echorouk_Homepage_Ajax
{
    /**
     * @var Echorouk_Homepage_Settings
     */
    protected $settings;

    /**
     * @param Echorouk_Homepage_Settings $settings Settings service.
     */
    public function __construct(Echorouk_Homepage_Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Register AJAX hooks.
     *
     * @return void
     */
    public function register_hooks()
    {
        add_action('wp_ajax_echorouk_homepage_get_config', [$this, 'get_config']);
        add_action('wp_ajax_echorouk_homepage_save_config', [$this, 'save_config']);
        add_action('wp_ajax_echorouk_homepage_reset_config', [$this, 'reset_config']);
        add_action('wp_ajax_echorouk_homepage_search_posts', [$this, 'search_posts']);
        add_action('wp_ajax_echorouk_homepage_latest_posts', [$this, 'latest_posts']);
    }

    /**
     * AJAX: fetch config.
     *
     * @return void
     */
    public function get_config()
    {
        $this->authorize();

        $config = $this->settings->get_config();
        $post_ids = $this->collect_post_ids($config);

        wp_send_json_success([
            'config'   => $config,
            'registry' => $this->settings->get_registered_sections(),
            'posts'    => $this->get_post_summaries($post_ids),
        ]);
    }

    /**
     * AJAX: save config.
     *
     * @return void
     */
    public function save_config()
    {
        $this->authorize();

        $raw = isset($_POST['config']) ? wp_unslash($_POST['config']) : '';

        if (! is_string($raw) || $raw === '') {
            wp_send_json_error([
                'message' => __('Invalid config payload.', 'echorouk-homepage'),
            ], 400);
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            wp_send_json_error([
                'message' => __('Config payload must be valid JSON.', 'echorouk-homepage'),
            ], 400);
        }

        $saved = $this->settings->update_config($decoded);

        wp_send_json_success([
            'message' => __('Homepage configuration saved.', 'echorouk-homepage'),
            'config'  => $saved,
            'posts'   => $this->get_post_summaries($this->collect_post_ids($saved)),
        ]);
    }

    /**
     * AJAX: reset config.
     *
     * @return void
     */
    public function reset_config()
    {
        $this->authorize();

        $config = $this->settings->reset_config();

        wp_send_json_success([
            'message' => __('Homepage configuration reset.', 'echorouk-homepage'),
            'config'  => $config,
            'posts'   => [],
        ]);
    }

    /**
     * AJAX: search latest published posts.
     *
     * @return void
     */
    public function search_posts()
    {
        $this->authorize();

        $term = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
        $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : 12;
        $recent_hours = isset($_GET['recent_hours']) ? absint($_GET['recent_hours']) : 48;
        $per_page = max(1, min(20, $per_page));
        $recent_hours = max(1, min(168, $recent_hours));

        $query_args = [
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $per_page,
            'ignore_sticky_posts'    => true,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'date_query'             => [
                [
                    'after' => sprintf('%d hours ago', $recent_hours),
                ],
            ],
        ];

        if ($term !== '') {
            $query_args['s'] = $term;
        }

        $query = new WP_Query($query_args);
        $results = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $results[] = $this->map_post_summary($post);
            }
        }

        wp_send_json_success([
            'items' => $results,
        ]);
    }

    /**
     * AJAX: fetch latest posts for dropdown presets.
     *
     * @return void
     */
    public function latest_posts()
    {
        $this->authorize();

        $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : 10;
        $per_page = max(1, min(20, $per_page));

        $query = new WP_Query([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'posts_per_page'         => $per_page,
            'ignore_sticky_posts'    => true,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $results = [];
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $results[] = $this->map_post_summary($post);
            }
        }

        wp_send_json_success([
            'items' => $results,
        ]);
    }

    /**
     * Collect all configured post IDs to preload labels.
     *
     * @param array<string, mixed> $config Config.
     * @return array<int, int>
     */
    protected function collect_post_ids(array $config)
    {
        $ids = [];

        if (empty($config['sections']) || ! is_array($config['sections'])) {
            return [];
        }

        foreach ($config['sections'] as $section) {
            if (! is_array($section)) {
                continue;
            }

            if (! empty($section['post_ids']) && is_array($section['post_ids'])) {
                $ids = array_merge($ids, array_map('absint', $section['post_ids']));
            }

            if (empty($section['meta']) || ! is_array($section['meta'])) {
                continue;
            }

            $meta = $section['meta'];

            foreach (['main_post_id', 'live_post_id'] as $single_key) {
                if (! empty($meta[$single_key])) {
                    $ids[] = absint($meta[$single_key]);
                }
            }

            foreach (['side_post_ids', 'fallback_post_ids', 'secondary_post_ids'] as $multi_key) {
                if (! empty($meta[$multi_key]) && is_array($meta[$multi_key])) {
                    $ids = array_merge($ids, array_map('absint', $meta[$multi_key]));
                }
            }
        }

        $ids = array_values(array_filter(array_unique($ids)));

        return $ids;
    }

    /**
     * Resolve post summaries keyed by ID.
     *
     * @param array<int, int> $post_ids IDs.
     * @return array<string, array<string, mixed>>
     */
    protected function get_post_summaries(array $post_ids)
    {
        if (empty($post_ids)) {
            return [];
        }

        $query = new WP_Query([
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'post__in'               => $post_ids,
            'posts_per_page'         => count($post_ids),
            'orderby'                => 'post__in',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'suppress_filters'       => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ]);

        $mapped = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $item = $this->map_post_summary($post);
                $mapped[(string) $post->ID] = $item;
            }
        }

        return $mapped;
    }

    /**
     * Build lightweight post descriptor.
     *
     * @param WP_Post $post Post.
     * @return array<string, mixed>
     */
    protected function map_post_summary($post)
    {
        return [
            'id'        => (int) $post->ID,
            'title'     => get_the_title($post->ID),
            'date'      => get_the_date('Y/m/d', $post->ID),
            'permalink' => get_permalink($post->ID),
        ];
    }

    /**
     * Enforce capability and nonce.
     *
     * @return void
     */
    protected function authorize()
    {
        if (! current_user_can('edit_theme_options')) {
            wp_send_json_error([
                'message' => __('You do not have permission.', 'echorouk-homepage'),
            ], 403);
        }

        check_ajax_referer('echorouk_homepage_nonce', 'nonce');
    }
}
