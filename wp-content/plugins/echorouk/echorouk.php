<?php
/**
 * Plugin Name: Echorouk Homepage Manager
 * Description: Editorial homepage section manager with drag-and-drop ordering and AJAX CRUD.
 * Version: 1.0.0
 * Author: Echorouk
 * Text Domain: echorouk-homepage
 */

if (! defined('ABSPATH')) {
    exit;
}

define('ECHOROUK_HOMEPAGE_VERSION', '1.0.0');
define('ECHOROUK_HOMEPAGE_FILE', __FILE__);
define('ECHOROUK_HOMEPAGE_PATH', plugin_dir_path(__FILE__));
define('ECHOROUK_HOMEPAGE_URL', plugin_dir_url(__FILE__));

require_once ECHOROUK_HOMEPAGE_PATH . 'includes/class-echorouk-homepage-settings.php';
require_once ECHOROUK_HOMEPAGE_PATH . 'includes/class-echorouk-homepage-ajax.php';
require_once ECHOROUK_HOMEPAGE_PATH . 'admin/class-echorouk-homepage-admin.php';
require_once ECHOROUK_HOMEPAGE_PATH . 'includes/class-echorouk-homepage-manager.php';
require_once ECHOROUK_HOMEPAGE_PATH . 'includes/template-tags.php';

register_activation_hook(ECHOROUK_HOMEPAGE_FILE, ['Echorouk_Homepage_Settings', 'activate']);

/**
 * Bootstrap plugin singleton.
 *
 * @return Echorouk_Homepage_Manager
 */
function echorouk_homepage_manager()
{
    return Echorouk_Homepage_Manager::instance();
}

echorouk_homepage_manager();
