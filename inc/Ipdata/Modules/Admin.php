<?php

namespace Ipdata\Modules;

use Ipdata\App;

/**
 * Admin functions
 */
class Admin extends Module
{
    /**
     * Constructor
     *
     * @param App $app
     */
    protected function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * Run
     */
    public function run()
    {
        $this->settings();
    }

    /**
     * Add Admin Settings page and Metabox to posts and pages
     */
    private function settings()
    {
        // Settings page fields
        $settingsFields = [
            [
                'id' => 'api_key',
                'type' => 'text',
                'label' => 'IpData API Key',
            ],
            [
                'id' => 'api_timeout',
                'type' => 'number',
                'label' => 'IpData API Timeout, sec',
                'min' => 1,
                'max' => 3600,
            ],
        ];

        // Metabox fields
        $mbFields = [
            [
                'id' => 'blocked_countries',
                'type' => 'select2',
                'multiple' => true,
                'ajax_action' => 's2_countries',
                'label_cb' => [$this, 's2CountryLabel'],
                'label' => 'Blocked countries',
            ],
            [
                'id' => 'redirect_page',
                'type' => 'select2',
                'ajax_action' => 's2_pages',
                'label_cb' => [$this, 's2PageLabel'],
                'label' => 'Page to Redirect',
            ],
            [
                'id' => 'rules',
                'type' => 'custom',
                'tpl' => 'rules',
                'countries' => $this->config['countries'],
            ],
        ];

        // Add Settings page
        $this->fw->addAdminPage([
            'id' => 'ipdata',
            'name' => 'IpData',
            'icon' => 'dashicons-update',
            'position' => 99,
            'tabs' => [
                [
                    'title' => 'Settings',
                    'form' => true,
                    'option' => 'settings',
                    'fields' => array_merge($settingsFields, $mbFields),
                ],
            ],
        ]);

        // Add individual posts/pages metabox
        $this->fw->addMetabox([
            'title' => 'IpData',
            'screen' => ['post', 'page'],
            'fields' => $mbFields,
        ]);
    }

    /**
     * Supply Country labels for Select2
     *
     * @param string $id
     * @return string
     */
    public function s2CountryLabel($id)
    {
        $countries = $this->config['countries'];

        return array_key_exists($id, $countries) ? $countries[$id] : $id;
    }

    /**
     * Supply Page labels for Select2
     *
     * @param string $id
     * @return string
     */
    public function s2PageLabel($id)
    {
        if (!$post = get_post($id)) {
            return $id;
        }

        return $post->post_title . " (#$id)";
    }
}
