<?php

namespace Ipdata;

use AlexDashkin\Adwpfw\Exceptions\AdwpfwException;
use Ipdata\Exceptions\IpdataException;
use Ipdata\Modules\Module;

/**
 * Main App Class
 */
class App
{
    /**
     * @var App Single instance
     */
    private static $instance;

    /**
     * @var \AlexDashkin\Adwpfw\App Framework
     */
    public $fw;

    /**
     * @var array App Config
     */
    public $config = [];

    /**
     * @var Module[]
     */
    private $modules = [];

    /**
     * App constructor
     *
     * @param array $modules
     * @throws IpdataException
     * @throws AdwpfwException
     */
    private function __construct(array $modules)
    {
        $this->config();

        $this->fw = new \AlexDashkin\Adwpfw\App($this->config);

        foreach ($modules as $module) {
            $this->getModule($module);
        }
    }

    /**
     * Init the App
     */
    public static function init()
    {
        try {

            self::the(['Assets', 'Admin', 'Ajax', 'Main', 'Shortcodes'])->run();

        } catch (\Exception $e) {

            $message = 'Exception in ' . basename(__FILE__) . ': ' . $e->getMessage();

            error_log($message);

            add_action('admin_notices', function () use ($message) {
                echo "<div class='error'><p><b>$message</b></p></div>";
            });
        }
    }

    /**
     * Run Modules
     */
    private function run()
    {
        foreach ($this->modules as $module) {
            $module->run();
        }
    }

    /**
     * Get Module
     *
     * @param string $module
     * @return Module
     * @throws IpdataException
     */
    public function getModule($module)
    {
        if (!array_key_exists($module, $this->modules)) {
            $class = '\\' . __NAMESPACE__ . '\\Modules\\' . $module;

            if (!class_exists($class)) {
                throw new IpdataException('Module ' . $class . ' not found');
            }

            $this->modules[$module] = $class::init($this);
        }

        return $this->modules[$module];
    }

    /**
     * Get Framework Module
     *
     * @param string $module
     * @return \AlexDashkin\Adwpfw\Modules\Module
     * @throws AdwpfwException
     */
    public function getFwModule($module)
    {
        return $this->fw->m($module);
    }

    /**
     * Init App Config
     */
    private function config()
    {
        $configPath = BASE_DIR . 'config/';
        $config = require $configPath . 'prod.php';
        $countries = require $configPath . 'countries.php';

        if ('dev' === ENV) {
            $dev = require $configPath . 'dev.php';
            $config = $this->arrayMerge($config, $dev);
        }

        /**
         * Filter list of countries used across all the App
         *
         * @param array $countries List of countries in ['AF' => 'Afghanistan'] format
         */
        $countries = apply_filters('ipdata_countries_list', $countries);

        $config = array_merge($config, [
            'baseDir' => BASE_DIR,
            'baseFile' => BASE_FILE,
            'baseUrl' => plugin_dir_url(BASE_FILE),
            'countries' => $countries,
        ]);

        if ($config['dev']) {
            error_reporting(E_ALL);

            $constants = [
                'WP_DEBUG' => true,
                'SCRIPT_DEBUG' => true,
                'WP_DEBUG_LOG' => true,
                'WP_DEBUG_DISPLAY' => false,
            ];

            foreach ($constants as $name => $value) {
                if (!defined($name)) {
                    define($name, $value);
                }
            }
        }

        $this->config = $config;
    }

    /**
     * Helper for deep array merge
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    private function arrayMerge(array $arr1, array $arr2)
    {
        foreach ($arr1 as $key => &$value) {
            if (!array_key_exists($key, $arr2)) {
                continue;
            }

            if (is_array($value) && is_array($arr2[$key])) {
                $value = $this->arrayMerge($value, $arr2[$key]);
            } else {
                $value = $arr2[$key];
            }
        }

        return $arr1;
    }

    /**
     * Get App instance
     *
     * @param array $modules
     * @return App
     * @throws IpdataException
     * @throws AdwpfwException
     */
    public static function the($modules = [])
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($modules);
        }

        return self::$instance;
    }
}
