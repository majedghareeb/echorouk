<?php

if (! defined('ABSPATH')) {
    exit;
}

class Echorouk_Homepage_Manager
{
    /**
     * @var Echorouk_Homepage_Manager|null
     */
    protected static $instance = null;

    /**
     * @var Echorouk_Homepage_Settings
     */
    protected $settings;

    /**
     * @var Echorouk_Homepage_Ajax
     */
    protected $ajax;

    /**
     * @var Echorouk_Homepage_Admin
     */
    protected $admin;

    /**
     * Singleton accessor.
     *
     * @return Echorouk_Homepage_Manager
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Bootstrap manager services.
     */
    protected function __construct()
    {
        $this->settings = new Echorouk_Homepage_Settings();
        $this->ajax = new Echorouk_Homepage_Ajax($this->settings);
        $this->admin = new Echorouk_Homepage_Admin($this->settings);

        add_action('plugins_loaded', [$this, 'load_textdomain']);

        $this->ajax->register_hooks();
        $this->admin->register_hooks();
    }

    /**
     * Load translations.
     *
     * @return void
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('echorouk-homepage', false, dirname(plugin_basename(ECHOROUK_HOMEPAGE_FILE)) . '/languages');
    }

    /**
     * Expose settings service.
     *
     * @return Echorouk_Homepage_Settings
     */
    public function settings()
    {
        return $this->settings;
    }
}
