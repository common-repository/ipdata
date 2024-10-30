<?php

namespace Ipdata\Modules;

use Ipdata\App;

/**
 * Main class
 */
class Main extends Module
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
     * Run
     */
    public function run()
    {
        // Env vars
        $this->setEnv();

        // Redirection
        add_action('template_redirect', [$this, 'redirect']);
    }

    /**
     * Process redirect rules
     */
    public function redirect()
    {
        // Skip admins, ajax and cron
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }

        // Get global redirect rules
        $rules = $this->parseRedirects($this->getOption('settings'));

        // Get queried object
        $object = get_queried_object();

        // If post - get object redirect rules
        if ($object instanceof \WP_Post) {
            $meta = $this->fw->metaboxGet('ipdata');

            $rules = array_merge($rules, $this->parseRedirects($meta));
        }

        // Return if no rules
        if (!$rules) {
            return;
        }

        // Get current user IP Data
        $userIpData = $this->m('Utils')->getUserIpData();

        // Return if not found
        if (empty($userIpData['country_code']) || empty($rules[$userIpData['country_code']])) {
            return;
        }

        $userCountryCode = $userIpData['country_code'];

        // Get redirect URL for user's country
        $redirectUrl = $rules[$userCountryCode];
        $this->log('Found redirection rule for country %s: %s', [$userCountryCode, $redirectUrl]);

        // Check redirect URL
        if (!wp_http_validate_url($redirectUrl)) {
            $this->log('Redirect URL is invalid: %s', [$redirectUrl]);
            return;
        }

        // Check current URL
        $currentUrl = home_url($GLOBALS['wp']->request);

        // If match - abort
        if (trim($redirectUrl, '/ ') === trim($currentUrl, '/ ')) {
            $this->log('Redirect URL %s is equal to current URL, redirection stopped', [$redirectUrl]);
            return;
        }

        /**
         * Allow disabling redirection
         *
         * @param bool $disable Return true to disable redirection
         * @param array $userIpData User IP Data retrieved from IPData API
         * @param string $currentUrl Requested URL
         * @param string $redirectUrl URL to redirect
         */
        if (apply_filters('ipdata_disable_redirection', false, $userIpData, $currentUrl, $redirectUrl)) {
            return;
        }

        // Redirecting
        $this->log('Redirecting to ' . $redirectUrl);

        if (wp_redirect($redirectUrl)) {
            die();
        }
    }

    /**
     * Prepare array of redirects
     *
     * @param array $settings
     * @return array
     */
    private function parseRedirects(array $settings)
    {
        $rules = [];

        // Parse blocked countries
        if (!empty($settings['blocked_countries']) && !empty($settings['redirect_page'])) {
            foreach ($settings['blocked_countries'] as $countryCode) {
                $redirectPageId = (int)$settings['redirect_page'];

                if ($redirectPageId && $url = get_permalink($redirectPageId)) {
                    $rules[$countryCode] = $url;
                } else {
                    $this->log('Wrong redirect page, rule skipped');
                }
            }
        }

        // Parse specific country rules
        if (!empty($settings['rules'])) {
            $rules = array_merge($rules, $this->fw->arrayParse($settings['rules'], ['url'], 'country'));
        }

        /**
         * Filter redirection rules
         *
         * @param array $rules Redirection rules in ['AF' => 'https://redirect.url'] format
         * @param array $settings User redirection settings
         */
        return apply_filters('ipdata_redirection_rules', $rules, $settings);
    }

    /**
     * Assign Environment Variables
     */
    private function setEnv()
    {
        // Get current user IP Data
        if (!$userIpData = $this->m('Utils')->getUserIpData()) {
            return;
        }

        // Define names mapping
        $env = [
            'HTTP_IPDATA_COUNTRY_CODE' => 'country_code',
            'HTTP_IPDATA_REGION' => 'region',
            'HTTP_IPDATA_CITY' => 'city',
        ];

        /**
         * Filter Environment Variables Mapping
         *
         * @param array $env Mapping env vars to IPData vars in ['ENV_VAR' => 'ipdata_var'] format
         * @param array $userIpData User IP Data retrieved from IPData API
         */
        $env = apply_filters('ipdata_env_vars_mapping', $env, $userIpData);

        // Assign vars
        foreach ($env as $name => $key) {
            if (array_key_exists($key, $userIpData)) {
                $value = $userIpData[$key];
                $result = putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}
