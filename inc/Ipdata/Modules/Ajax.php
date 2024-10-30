<?php

namespace Ipdata\Modules;

use Ipdata\App;

class Ajax extends Module
{
    /**
     * Ajax constructor.
     * @param App $app
     */
    protected function __construct(App $app)
    {
        parent::__construct($app);
    }

    /**
     * Add AJAX actions
     */
    public function run()
    {
        $actions = [
            [
                'name' => 's2_countries',
                'fields' => [
                    'q' => [
                        'required' => true,
                    ]
                ],
                'callback' => [$this, 's2Countries']
            ],
            [
                'name' => 's2_pages',
                'fields' => [
                    'q' => [
                        'required' => true,
                    ]
                ],
                'callback' => [$this, 's2Pages']
            ],
        ];

        $this->fw->addAjaxActions($actions);
    }

    /**
     * Supply countries for Select2
     *
     * @param array $data
     * @return array
     */
    public function s2Countries(array $data)
    {
        $results = [];
        $query = $data['q'];

        foreach ($this->config['countries'] as $code => $country) {
            if (false !== stripos($code, $query) || false !== stripos($country, $query)) {
                $results[] = [
                    'id' => $code,
                    'text' => $country,
                ];
            }
        }

        /**
         * Filter list of countries for select2 search
         *
         * @param array $results List of countries in ['id' => 'AF', 'text' => 'Afghanistan'] format
         * @param string $query Search query
         */
        $results = apply_filters('ipdata_search_countries', $results, $query);

        return $this->success('Done', $results);
    }

    /**
     * Supply Pages for Select2
     *
     * @param array $data
     * @return array
     */
    public function s2Pages(array $data)
    {
        if (empty($data['q']) || strlen($data['q']) < 3) {
            return $this->error('Invalid request');
        }

        $query = trim($data['q']);
        $results = [];

        $pages = $this->fw->dbSelect('posts', ['ID', 'post_title'], ['post_type' => 'page'], [], false, false);

        foreach ($pages as $page) {
            foreach ($page as $entry) {
                if (false !== stripos($entry, $query)) {
                    $id = $page['ID'];
                    $text = $page['post_title'] . ' (#' . $id . ')';
                    $results[$id] = [
                        'id' => $id,
                        'text' => $text,
                    ];
                }
            }
        }

        /**
         * Filter list of pages for select2 search
         *
         * @param array $results List of pages in ['id' => 1, 'text' => 'Page Title'] format
         * @param string $query Search query
         */
        $results = apply_filters('ipdata_search_pages', array_values($results), $query);

        return $this->success('Done', $results);
    }

    /**
     * Return AJAX success
     *
     * @param string $message
     * @param array $data
     * @return array
     */
    private function success($message = '', array $data = [])
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Return AJAX error
     *
     * @param string $message
     * @return array
     */
    private function error($message = '')
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}
