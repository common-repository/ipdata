<?php

namespace Ipdata\Modules;

use Ipdata\App;

/**
 * Main class
 */
class Shortcodes extends Module
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
     * Add shortcodes
     */
    public function run()
    {
        $this->fw->addShortcodes([
            [
                'id' => 'continent',
                'callback' => function () {
                    return $this->getIpDataValue('continent_code');
                },
            ],
            [
                'id' => 'country',
                'callback' => function () {
                    return $this->getIpDataValue('country_code');
                },
            ],
            [
                'id' => 'region',
                'callback' => function () {
                    return $this->getIpDataValue('region');
                },
            ],
            [
                'id' => 'city',
                'callback' => function () {
                    return $this->getIpDataValue('city');
                },
            ],
            [
                'id' => 'location',
                'callback' => function () {
                    $lat = $this->getIpDataValue('latitude');
                    $long = $this->getIpDataValue('longitude');
                    return $lat && $long ? sprintf('%s/%s', $lat, $long) : '';
                },
            ],
        ]);
    }

    /**
     * Get IP Data value by key
     *
     * @param string $key
     * @return string
     */
    private function getIpDataValue($key)
    {
        $userIpData = $this->m('Utils')->getUserIpData();

        $output = array_key_exists($key, $userIpData) ? $userIpData[$key] : '';

        /**
         * Filter shortcode output
         *
         * @param string $output Shortcode output
         * @param array $userIpData User IP Data retrieved from IPData API
         * @param string $key Requested key
         */
        return apply_filters('ipdata_shortcode_output', $output, $userIpData, $key);
    }
}
