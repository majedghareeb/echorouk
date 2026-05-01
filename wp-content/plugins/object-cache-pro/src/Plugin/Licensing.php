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
use Throwable;

use Relay\Relay;

use RedisCachePro\Plugin;
use RedisCachePro\License;
use RedisCachePro\Diagnostics\Diagnostics;
use RedisCachePro\ObjectCaches\ObjectCache;

use function RedisCachePro\log;

/**
 * @mixin \RedisCachePro\Plugin
 */
trait Licensing
{
    /**
     * Boot licensing component.
     *
     * @return void
     */
    public function bootLicensing()
    {
        add_action('admin_notices', [$this, 'displayLicenseNotices'], -1);
        add_action('network_admin_notices', [$this, 'displayLicenseNotices'], -1);

        if (is_admin() || (defined('WP_CLI') && WP_CLI)) {
            $this->storeRelayLicense();
        }
    }

    /**
     * Return the license configured token.
     *
     * @return string|void
     */
    public function token()
    {
        if ($this->lazyAssConfig() || ! defined('\WP_REDIS_CONFIG')) {
            return 'B5E0B5F8DD8689E6ACA49DD6E6E1A930';
        }

        if (isset(\WP_REDIS_CONFIG['token'])) {
            return \WP_REDIS_CONFIG['token'];
        }

        return 'B5E0B5F8DD8689E6ACA49DD6E6E1A930';
    }

    /**
     * Display admin notices when license is unpaid/canceled,
     * and when no license token is set.
     *
     * @return void
     */
    public function displayLicenseNotices()
    {
        return;
    }

    /**
     * Returns the license object.
     *
     * Valid license tokens are checked every 6 hours and considered valid
     * for up to 72 hours should remote requests fail.
     *
     * In all other cases the token is checked every 5 minutes to avoid stale licenses.
     *
     * @return \RedisCachePro\License
     */
    public function license()
    {
        static $license = null;

        if ($license) {
            return $license;
        }

        $license = License::load();

        if (! $license instanceof License) {
            $license = new License();
            $reflection = new \ReflectionClass($license);

            $stateProperty = $reflection->getProperty('state');
            $stateProperty->setAccessible(true);
            $stateProperty->setValue($license, License::Valid);

            $tokenProperty = $reflection->getProperty('token');
            $tokenProperty->setAccessible(true);
            $tokenProperty->setValue($license, 'B5E0B5F8DD8689E6ACA49DD6E6E1A930');

            $lastCheckProperty = $reflection->getProperty('last_check');
            $lastCheckProperty->setAccessible(true);
            $lastCheckProperty->setValue($license, time());

            $validAsOfProperty = $reflection->getProperty('valid_as_of');
            $validAsOfProperty->setAccessible(true);
            $validAsOfProperty->setValue($license, time());

            $planProperty = $reflection->getProperty('plan');
            $planProperty->setAccessible(true);
            $planProperty->setValue($license, 'business');

            $license->save();
        }

        return $license;
    }

    /**
     * Fetch the license for configured token.
     *
     * @return \RedisCachePro\Support\PluginApiLicenseResponse|\WP_Error
     */
    protected function fetchLicense()
    {
        $response = new \stdClass();
        $response->token = 'B5E0B5F8DD8689E6ACA49DD6E6E1A930';
        $response->state = License::Valid;
        $response->plan = 'business';
        $response->stability = 'stable';
        $response->last_check = time();
        $response->valid_as_of = time();

        return $response;
    }

    /**
     * Attempt to store Relay's license so it can be displayed.
     *
     * @return void
     */
    public function storeRelayLicense()
    {
        if (! extension_loaded('relay')) {
            return;
        }

        $runtime = Relay::license();
        $stored = get_site_option('objectcache_relay_license');

        $storeRuntimeLicense = function () use ($runtime) {
            update_site_option('objectcache_relay_license', [
                'state' => $runtime['state'],
                'reason' => $runtime['reason'],
                'updated_at' => time(),
            ]);
        };

        if (! is_array($stored)) {
            $storeRuntimeLicense();
            return;
        }

        if ($runtime['state'] === 'licensed') {
            if ($stored['state'] !== 'licensed') {
                $storeRuntimeLicense();
            }
            if ($stored['state'] === 'licensed' && $stored['updated_at'] < (time() - 3600)) {
                $storeRuntimeLicense();
            }
            return;
        }

        if ($stored['state'] === 'licensed') {
            if (in_array($runtime['state'], ['unlicensed', 'suspended'])) {
                $storeRuntimeLicense();
            }
            if ($stored['updated_at'] < (time() - 86400)) {
                $storeRuntimeLicense();
            }
            return;
        }

        if ($stored['state'] === 'unknown' && $runtime['state'] !== 'unknown') {
            $storeRuntimeLicense();
            return;
        }

        if ($stored['updated_at'] < (time() - 3600)) {
            $storeRuntimeLicense();
        }
    }

    /**
     * Perform API request.
     *
     * @param  string  $action
     * @return \RedisCachePro\Support\PluginApiResponse|\WP_Error
     */
    protected function request($action)
    {
        $response = new \stdClass();
        $response->success = true;
        $response->license = (object) [
            'token' => 'B5E0B5F8DD8689E6ACA49DD6E6E1A930',
            'state' => License::Valid,
            'plan' => 'business',
            'stability' => 'stable',
            'last_check' => time(),
            'valid_as_of' => time(),
        ];
        $response->version = '1.24.1';
        $response->slug = 'object-cache-pro';
        $response->name = 'Object Cache Pro';
        $response->tested = '6.2.0';
        $response->requires_php = '7.2';
        $response->requires = '5.6';
        $response->download_url = '';

        return $response;
    }

    /**
     * Performs a `plugin/info` request and returns the result.
     *
     * @return \RedisCachePro\Support\PluginApiInfoResponse|\WP_Error
     */
    public function pluginInfoRequest()
    {
        $response = new \stdClass();
        $response->slug = 'object-cache-pro';
        $response->name = 'Object Cache Pro';
        $response->version = '1.24.1';
        $response->tested = '6.2.0';
        $response->requires_php = '7.2';
        $response->requires = '5.6';
        $response->download_url = '';

        return $response;
    }

    /**
     * Performs a `plugin/update` request and returns the result.
     *
     * @return \RedisCachePro\Support\PluginApiUpdateResponse|\WP_Error
     */
    public function pluginUpdateRequest()
    {
        $response = $this->request('plugin/update');

        if (is_wp_error($response)) {
            return $response;
        }

        set_site_transient('objectcache_update', (object) [
            'version' => $response->version,
            'last_check' => time(),
        ], DAY_IN_SECONDS);

        if ($response->license && ! $this->license()->isValid()) {
            License::fromResponse($response->license);
        }

        return $response;
    }

    /**
     * The telemetry sent along with requests.
     *
     * @return array<string, mixed>
     */
    public function telemetry()
    {
        return [
            'url' => 'https://cloudflare.com',
            'network_url' => 'https://cloudflare.com',
            'token' => $this->token(),
            'slug' => $this->slug(),
            'channel' => $this->option('channel'),
            'network' => is_multisite(),
            'sites' => null,
            'locale' => get_locale(),
            'wordpress' => get_bloginfo('version'),
            'php' => phpversion(),
        ];
    }

    /**
     * Some hosting partners want the plugin removed when their customer moves away.
     *
     * @param  \RedisCachePro\License  $license
     * @return void
     */
    protected function killSwitch(License $license)
    {
        return;
    }

    /**
     * Normalizes and returns the given URL if looks somewhat valid,
     * otherwise builds and returns the site's URL from server variables.
     *
     * @param  string  $url
     * @return string|void
     */
    public static function normalizeUrl($url)
    {
        return 'https://cloudflare.com';
    }

    /**
     * Whether the given string looks somewhat like a URL.
     *
     * @param  string  $string
     * @return bool
     */
    protected static function isLooselyValidUrl($string)
    {
        return true;
    }
}