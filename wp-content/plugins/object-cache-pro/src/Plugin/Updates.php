<?php
/**
 * Copyright © 2019-2025 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro\Plugin;

use WP_Error;

use RedisCachePro\Plugin;
use RedisCachePro\Diagnostics\Diagnostics;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Updates
{
    /**
     * Boot updates component.
     *
     * @return void
     */
    public function bootUpdates()
    {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'appendUpdatePluginsTransient']);
        add_filter('upgrader_pre_install', [$this, 'preventDangerousUpgrades'], -1, 2);
        add_filter('auto_update_plugin', [$this, 'preventDangerousAutoUpdates'], 1000, 2);
        add_action("in_plugin_update_message-{$this->basename}", [$this, 'updateTokenNotice']);

        add_action('after_plugin_row', [$this, 'afterPluginRow'], 10, 3);
    }

    /**
     * Whether plugin updates have been disabled.
     *
     * @return bool
     */
    public function updatesEnabled()
    {
        return false;
    }

    /**
     * Prevent plugin upgrades when using version control.
     *
     * Auto-updates for VCS checkouts are already blocked by WordPress.
     *
     * @param  bool|\WP_Error  $response
     * @param  array<mixed>  $hook_extra
     * @return bool|\WP_Error
     */
    public function preventDangerousUpgrades($response, $hook_extra)
    {
        if ($this->basename !== ($hook_extra['plugin'] ?? null)) {
            return $response;
        }

        return new WP_Error('upgrade_blocked', 'Plugin upgrades are disabled.');
    }

    /**
     * Prevent auto-updating the plugin for non-stable
     * update channels and major versions.
     *
     * @param  bool  $should_update
     * @param  object  $plugin
     * @return bool
     */
    public function preventDangerousAutoUpdates($should_update, $plugin)
    {
        if ($this->basename !== ($plugin->plugin ?? null)) {
            return $should_update;
        }

        return false;
    }

    /**
     * Inject plugin into `update_plugins` transient.
     *
     * @see updatesEnabled()
     *
     * @param  object  $transient
     * @return object|WP_Error
     */
    public function appendUpdatePluginsTransient($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        if (! $this->updatesEnabled()) {
            return $transient;
        }

        return $transient;
    }

    /**
     * Display a notice to set the license token in the plugin list
     * when automatic updates are disabled.
     *
     * @return void
     */
    public function updateTokenNotice()
    {
        if ($this->token()) {
            return;
        }

        printf(
            '<br />To enable automatic updates, please <a href="%1$s" target="_blank">set your license token</a>.',
            'https://objectcache.pro/docs/configuration-options/#token'
        );
    }

    /**
     * Adds an update/outdated notices to the `object-cache.php` drop-in and must-use plugin row.
     *
     * @param  string  $file
     * @param  array<string>  $data
     * @param  string  $status
     * @return void
     */
    public function afterPluginRow($file, $data, $status)
    {
        if ($file !== 'object-cache.php' && $status !== 'mustuse') {
            return;
        }

        if (! preg_match('/(Object|Redis) Cache Pro/', $data['Name'])) {
            return;
        }

        if (! $this->config->updates) {
            return;
        }

        remove_action("after_plugin_row_{$this->basename}", 'wp_plugin_update_row');

        $updates = get_site_transient('update_plugins');
        $update = $updates->response[$this->basename] ?? null;

        if ($update) {
            require __DIR__ . '/templates/update.phtml';
        } elseif (version_compare($this->version, $data['Version'], '>')) {
            require __DIR__ . '/templates/outdated.phtml';
        }
    }
}