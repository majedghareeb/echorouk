<?php

if (! defined('ABSPATH')) {
    exit;
}

class Echorouk_Homepage_Admin
{
    /**
     * @var Echorouk_Homepage_Settings
     */
    protected $settings;

    /**
     * @var string
     */
    protected $page_hook = '';
    /**
     * @var string
     */
    protected $about_hook = '';

    /**
     * @param Echorouk_Homepage_Settings $settings Settings service.
     */
    public function __construct(Echorouk_Homepage_Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Register admin hooks.
     *
     * @return void
     */
    public function register_hooks()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Register plugin menu page.
     *
     * @return void
     */
    public function register_menu()
    {
        $this->page_hook = add_menu_page(
            __('Homepage Editorial Panel', 'echorouk-homepage'),
            __('Homepage Editor', 'echorouk-homepage'),
            'edit_theme_options',
            'echorouk-homepage-editor',
            [$this, 'render_page'],
            'dashicons-screenoptions',
            59
        );

        $this->about_hook = add_submenu_page(
            'echorouk-homepage-editor',
            __('Homepage Theme Integration', 'echorouk-homepage'),
            __('About / Integration', 'echorouk-homepage'),
            'edit_theme_options',
            'echorouk-homepage-about',
            [$this, 'render_about_page']
        );
    }

    /**
     * Enqueue scripts for plugin page.
     *
     * @param string $hook Current admin hook.
     * @return void
     */
    public function enqueue_assets($hook)
    {
        $is_editor_page = ($this->page_hook === $hook);
        $is_about_page = ($this->about_hook === $hook);

        if (! $is_editor_page && ! $is_about_page) {
            return;
        }

        wp_enqueue_style(
            'echorouk-homepage-editor',
            ECHOROUK_HOMEPAGE_URL . 'assets/css/editor.css',
            [],
            ECHOROUK_HOMEPAGE_VERSION
        );

        if (! $is_editor_page) {
            return;
        }

        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_script(
            'echorouk-homepage-editor',
            ECHOROUK_HOMEPAGE_URL . 'assets/js/editor.js',
            ['jquery', 'jquery-ui-sortable'],
            ECHOROUK_HOMEPAGE_VERSION,
            true
        );

        wp_localize_script('echorouk-homepage-editor', 'EchoroukHomepageEditor', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('echorouk_homepage_nonce'),
            'i18n'    => [
                'loading'        => __('Loading configuration...', 'echorouk-homepage'),
                'save'           => __('Save Changes', 'echorouk-homepage'),
                'saving'         => __('Saving...', 'echorouk-homepage'),
                'reset'          => __('Reset Defaults', 'echorouk-homepage'),
                'searchPlaceholder' => __('Search posts...', 'echorouk-homepage'),
                'manualSelection'   => __('Manual Selection', 'echorouk-homepage'),
                'latestPosts'       => __('Latest Posts', 'echorouk-homepage'),
                'enabled'           => __('Enabled', 'echorouk-homepage'),
                'limit'             => __('Items Limit', 'echorouk-homepage'),
                'source'            => __('Content Source', 'echorouk-homepage'),
                'add'               => __('Add', 'echorouk-homepage'),
                'remove'            => __('Remove', 'echorouk-homepage'),
                'liveCoverage'      => __('Live Coverage', 'echorouk-homepage'),
                'mainArticle'       => __('Main Article', 'echorouk-homepage'),
                'sideArticles'      => __('Side Articles', 'echorouk-homepage'),
                'fallbackArticles'  => __('Fallback Articles', 'echorouk-homepage'),
                'videoUrl'          => __('Video URL', 'echorouk-homepage'),
                'autoplay'          => __('Autoplay', 'echorouk-homepage'),
                'searchNoResults'   => __('No posts found.', 'echorouk-homepage'),
                'saveSuccess'       => __('Saved successfully.', 'echorouk-homepage'),
                'saveError'         => __('Unable to save configuration.', 'echorouk-homepage'),
                'resetConfirm'      => __('Reset to defaults?', 'echorouk-homepage'),
            ],
        ]);
    }

    /**
     * Render admin page.
     *
     * @return void
     */
    public function render_page()
    {
        if (! current_user_can('edit_theme_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'echorouk-homepage'));
        }

        include ECHOROUK_HOMEPAGE_PATH . 'admin/views/editor-page.php';
    }

    /**
     * Render integration/about page.
     *
     * @return void
     */
    public function render_about_page()
    {
        if (! current_user_can('edit_theme_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'echorouk-homepage'));
        }

        include ECHOROUK_HOMEPAGE_PATH . 'admin/views/about-page.php';
    }
}
