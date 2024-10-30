<?php

namespace Ipdata\Modules;

use Ipdata\App;

/**
 * Manage Assets
 */
class Assets extends Module
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
     * Add assets
     */
    public function run()
    {
        // Include on certain pages only
        $cb = function () {
            return in_array(get_current_screen()->id, ['toplevel_page_ipdata', 'post', 'page']);
        };

        // Prepare countries for JS
        $countries = [];

        foreach ($this->config['countries'] as $key => $value) {
            $countries[] = [
                'id' => $key,
                'text' => $value,
            ];
        }

        // Enqueue assets
        $assets = [
            [
                'type' => 'css',
                'af' => 'admin',
                'file' => 'assets/css/admin/app.css',
                'callback' => $cb,
            ],
            [
                'type' => 'js',
                'af' => 'admin',
                'file' => 'assets/js/admin/app.js',
                'callback' => $cb,
                'localize' => [
                    'countries' => $countries,
                ],
            ],
        ];

        $this->fw->addAssets($assets);
    }
}
