<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('echorouk_homepage_register_ticker_meta_box')) {
    /**
     * Register ticker meta box on post editor.
     *
     * @return void
     */
    function echorouk_homepage_register_ticker_meta_box()
    {
        add_meta_box(
            'echorouk-news-ticker-meta',
            __('News Ticker', 'echorouk-homepage'),
            'echorouk_homepage_render_ticker_meta_box',
            'post',
            'side',
            'high'
        );
    }
    add_action('add_meta_boxes', 'echorouk_homepage_register_ticker_meta_box');
}

if (! function_exists('echorouk_homepage_render_ticker_meta_box')) {
    /**
     * Render ticker controls.
     *
     * @param WP_Post $post Post object.
     * @return void
     */
    function echorouk_homepage_render_ticker_meta_box($post)
    {
        wp_nonce_field('echorouk_news_ticker_meta', 'echorouk_news_ticker_nonce');

        $enabled = get_post_meta($post->ID, 'echorouk_news_ticker_enabled', true);
        $type = sanitize_key((string) get_post_meta($post->ID, 'echorouk_news_ticker_type', true));
        if (! in_array($type, ['normal', 'breaking'], true)) {
            $type = 'normal';
        }
        ?>
		<p>
			<label>
				<input type="checkbox" name="echorouk_news_ticker_enabled" value="1" <?php checked('1', (string) $enabled); ?>>
				<?php esc_html_e('Show this post in the header news ticker', 'echorouk-homepage'); ?>
			</label>
		</p>
		<p>
			<label for="echorouk-news-ticker-type"><strong><?php esc_html_e('Ticker Type', 'echorouk-homepage'); ?></strong></label>
			<select id="echorouk-news-ticker-type" name="echorouk_news_ticker_type" class="widefat">
				<option value="normal" <?php selected($type, 'normal'); ?>><?php esc_html_e('Normal (Gold)', 'echorouk-homepage'); ?></option>
				<option value="breaking" <?php selected($type, 'breaking'); ?>><?php esc_html_e('Breaking (Red)', 'echorouk-homepage'); ?></option>
			</select>
		</p>
		<?php
    }
}

if (! function_exists('echorouk_homepage_save_ticker_meta_box')) {
    /**
     * Save ticker controls.
     *
     * @param int $post_id Post ID.
     * @return void
     */
    function echorouk_homepage_save_ticker_meta_box($post_id)
    {
        if (! isset($_POST['echorouk_news_ticker_nonce'])) {
            return;
        }

        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['echorouk_news_ticker_nonce'])), 'echorouk_news_ticker_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $enabled = isset($_POST['echorouk_news_ticker_enabled']) ? '1' : '0';
        update_post_meta($post_id, 'echorouk_news_ticker_enabled', $enabled);

        $type = isset($_POST['echorouk_news_ticker_type']) ? sanitize_key(wp_unslash($_POST['echorouk_news_ticker_type'])) : 'normal';
        if (! in_array($type, ['normal', 'breaking'], true)) {
            $type = 'normal';
        }
        update_post_meta($post_id, 'echorouk_news_ticker_type', $type);
    }
    add_action('save_post_post', 'echorouk_homepage_save_ticker_meta_box');
}

if (! function_exists('echorouk_homepage_register_ticker_bulk_actions')) {
    /**
     * Register news ticker bulk actions on posts list.
     *
     * @param array<string, string> $bulk_actions Bulk actions.
     * @return array<string, string>
     */
    function echorouk_homepage_register_ticker_bulk_actions($bulk_actions)
    {
        $bulk_actions['echorouk_ticker_add_normal'] = __('Add to News Ticker (Normal)', 'echorouk-homepage');
        $bulk_actions['echorouk_ticker_add_breaking'] = __('Add to News Ticker (Breaking)', 'echorouk-homepage');
        $bulk_actions['echorouk_ticker_remove'] = __('Remove from News Ticker', 'echorouk-homepage');

        return $bulk_actions;
    }
    add_filter('bulk_actions-edit-post', 'echorouk_homepage_register_ticker_bulk_actions');
}

if (! function_exists('echorouk_homepage_handle_ticker_bulk_actions')) {
    /**
     * Handle news ticker bulk actions.
     *
     * @param string   $redirect_to Redirect URL.
     * @param string   $doaction    Selected action.
     * @param int[]    $post_ids    Selected post IDs.
     * @return string
     */
    function echorouk_homepage_handle_ticker_bulk_actions($redirect_to, $doaction, $post_ids)
    {
        $supported = array(
            'echorouk_ticker_add_normal',
            'echorouk_ticker_add_breaking',
            'echorouk_ticker_remove',
        );

        if (! in_array($doaction, $supported, true)) {
            return $redirect_to;
        }

        $updated = 0;
        $mode = 'remove';
        if ('echorouk_ticker_add_normal' === $doaction) {
            $mode = 'normal';
        } elseif ('echorouk_ticker_add_breaking' === $doaction) {
            $mode = 'breaking';
        }

        foreach ($post_ids as $post_id) {
            $post_id = absint($post_id);
            if ($post_id < 1 || ! current_user_can('edit_post', $post_id)) {
                continue;
            }

            if ('remove' === $mode) {
                update_post_meta($post_id, 'echorouk_news_ticker_enabled', '0');
            } else {
                update_post_meta($post_id, 'echorouk_news_ticker_enabled', '1');
                update_post_meta($post_id, 'echorouk_news_ticker_type', $mode);
            }

            $updated++;
        }

        return add_query_arg(
            array(
                'echorouk_ticker_bulk_updated' => $updated,
                'echorouk_ticker_bulk_mode'    => $mode,
            ),
            $redirect_to
        );
    }
    add_filter('handle_bulk_actions-edit-post', 'echorouk_homepage_handle_ticker_bulk_actions', 10, 3);
}

if (! function_exists('echorouk_homepage_ticker_bulk_admin_notice')) {
    /**
     * Show admin notice after ticker bulk actions.
     *
     * @return void
     */
    function echorouk_homepage_ticker_bulk_admin_notice()
    {
        if (! is_admin()) {
            return;
        }

        if (! isset($_GET['echorouk_ticker_bulk_updated'])) {
            return;
        }

        $post_type = isset($_GET['post_type']) ? sanitize_key((string) $_GET['post_type']) : 'post';
        if ('post' !== $post_type) {
            return;
        }

        $count = absint($_GET['echorouk_ticker_bulk_updated']);
        $mode = isset($_GET['echorouk_ticker_bulk_mode']) ? sanitize_key((string) $_GET['echorouk_ticker_bulk_mode']) : 'normal';

        if ($count < 1) {
            return;
        }

        if ('remove' === $mode) {
            $message = sprintf(
                /* translators: %d: posts count */
                _n('%d post removed from News Ticker.', '%d posts removed from News Ticker.', $count, 'echorouk-homepage'),
                $count
            );
        } elseif ('breaking' === $mode) {
            $message = sprintf(
                /* translators: %d: posts count */
                _n('%d post added to News Ticker as Breaking.', '%d posts added to News Ticker as Breaking.', $count, 'echorouk-homepage'),
                $count
            );
        } else {
            $message = sprintf(
                /* translators: %d: posts count */
                _n('%d post added to News Ticker as Normal.', '%d posts added to News Ticker as Normal.', $count, 'echorouk-homepage'),
                $count
            );
        }

        ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html($message); ?></p>
		</div>
		<?php
    }
    add_action('admin_notices', 'echorouk_homepage_ticker_bulk_admin_notice');
}
