<?php
/*
Plugin Name:   Ipdata Integration Plugin
Plugin URI:    https://ipdata.co
Description:   Locate website visitors by IP Address to localize your website content, analyze logs, enrich forms, target ads, enforce GDPR compliance, perform redirections, block countries and more.
Version:       1.0.0
Author:        Jonathan Kosgei
Author URI:    https://jonathankosgei.com
License:       GPLv2 or later
*/

namespace Ipdata;

// In case of direct access - check for emergency deactivation request
if (!defined('ABSPATH')) {
    if (isset($_GET['deactivate']) && 'f6c06c66a174c82fa378be6c7a710d05' === md5($_GET['deactivate'])) {
        rename(__DIR__, __DIR__ . '_');
        die('Plugin dir renamed');
    }

    die('Direct access is forbidden');
}

// Define core constants
define(__NAMESPACE__ . '\\ENV', 'prod');
define(__NAMESPACE__ . '\\BASE_DIR', __DIR__ . '/');
define(__NAMESPACE__ . '\\BASE_FILE', __FILE__);

// Composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Own Autoloader
spl_autoload_register(function ($class) {
    $path = trim(str_replace('\\', '/', $class), '/');
    $file = __DIR__ . '/inc/' . $path . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}, false);

// Init the App
App::init();
