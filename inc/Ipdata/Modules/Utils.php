<?php

namespace Ipdata\Modules;

use Ipdata\App;

/**
 * Utility functions
 */
class Utils extends Module
{
    /**
     * Constructor
     *
     * @param App $app
     */
    protected function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * Get current user IP Data
     *
     * @return array
     */
    public function getUserIpData()
    {
        // Alternate IP sources
        $ipSources = [
            'HTTP_CF_CONNECTING_IP' // Cloudflare
        ];

        /**
         * Filter alternate IP sources
         *
         * @param array $ipSources list of keys for $_SERVER[] array
         */
        $ipSources = apply_filters('ipdata_ip_sources', $ipSources);

        // Get user IP
        $ip = $_SERVER['REMOTE_ADDR'];

        // Check alternate IP sources and use one if found
        foreach ($ipSources as $ipKey) {
            if (!empty($_SERVER[$ipKey])) {
                $ip = $_SERVER[$ipKey];
            }
        }

        // Check cache
        if ($cache = get_transient('ipdata_cache') ?: []) {
            if (!empty($cache[$ip])) {
                return $cache[$ip];
            }
        }

        $settings = $this->getOption('settings');

        /**
         * Filter user settings before making API call
         *
         * @param array $settings
         */
        $settings = apply_filters('ipdata_api_request_settings', $settings);

        // Check API key
        if (empty($settings['api_key'])) {
//            $this->log('API Key is empty, exit');
            return [];
        }

        /**
         * Filter requested fields before making API call
         *
         * @param string $fields
         */
        $fields = apply_filters('ipdata_api_request_fields', 'continent_code,country_code,region,city,latitude,longitude');

        // Call Ipdata API
        $response = $this->fw->apiRequest([
            'url' => $this->config['api_url'] . $ip,
            'timeout' => (int)$settings['api_timeout'] ?: 5,
            'data' => [
                'api-key' => $settings['api_key'],
                'fields' => $fields
            ],
        ]);

        // Return if error
        if (!$response || !$body = json_decode($response, true)) {
            $this->log('API request error');
            return [];
        }

        // Update cache
        $cache[$ip] = $body;
        set_transient('ipdata_cache', $cache, $this->config['cache_ttl']);

        return $body;
    }
}
