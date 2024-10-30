<?php

namespace Ipdata\Modules;

use Ipdata\App;

/**
 * Basic Module Class
 */
abstract class Module
{
    /**
     * @var array Modules
     */
    protected static $instances = [];

    /**
     * @var App
     */
    protected $app;

    /**
     * @var \AlexDashkin\Adwpfw\App Framework
     */
    protected $fw;
    /**
     * @var array App config
     */
    protected $config;

    /**
     * Module constructor
     *
     * @param App $app
     */
    protected function __construct(App $app)
    {
        $this->app = $app;
        $this->fw = $app->fw;
        $this->config = $app->config;
    }

    /**
     * Init Module
     *
     * @param App $app
     * @return Module
     */
    public static function init(App $app)
    {
        $class = strtolower(get_called_class());

        if (!isset(static::$instances[$class])) {
            static::$instances[$class] = new static($app);
        }

        return static::$instances[$class];
    }

    /**
     * Get Module
     *
     * @param string $module
     * @return Module
     * @throws \Ipdata\Exceptions\IpdataException
     */
    protected function m($module)
    {
        return $this->app->getModule($module);
    }

    /**
     * Get Framework module
     *
     * @param string $module
     * @return \AlexDashkin\Adwpfw\Modules\Module
     * @throws \AlexDashkin\Adwpfw\Exceptions\AdwpfwException
     */
    protected function fwm($module)
    {
        return $this->app->getFwModule($module);
    }

    /**
     * Add log entry
     *
     * @param mixed $message
     * @param int $type
     */
    protected function log($message, $type = 4)
    {
        $this->fw->log($message, $type);
    }

    /**
     * WP_Error handler
     *
     * @param mixed $result
     * @param string $errorMessage
     * @return mixed
     */
    protected function pr($result, $errorMessage = '')
    {
        return $this->fw->pr($result, $errorMessage);
    }

    /**
     * Get prefixed option
     *
     * @param string $name
     * @return mixed
     */
    protected function getOption($name)
    {
        $value = get_option($this->config['prefix'] . '_' . $name) ?: [];

        /**
         * Filter get option
         *
         * @param mixed $value Option value
         * @param string $name Option name
         */
        return apply_filters('ipdata_get_option', $value, $name);
    }

    /**
     * Update prefixed option
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    protected function updateOption($name, $value)
    {
        /**
         * Filter update option
         *
         * @param mixed $value Option value
         * @param string $name Option name
         */
        $value = apply_filters('ipdata_update_option', $value, $name);

        return update_option($this->config['prefix'] . '_' . $name, $value);
    }

    /**
     * Run the Module
     */
    public function run()
    {
    }
}