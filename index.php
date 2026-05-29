<?php
/**
 * AuraPHP - Minimal PHP Framework
 * 
 * @package AuraPHP
 */

// Define paths
define('SYSTEM_PATH', __DIR__ . '/system/');
define('SITE_PATH', __DIR__ . '/site/');

// Use Composer autoloader if available
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Load core framework files (lowercase filenames)
require_once SYSTEM_PATH . 'core/base.php';
require_once SYSTEM_PATH . 'core/controller.php';
require_once SYSTEM_PATH . 'core/model.php';
require_once SYSTEM_PATH . 'helpers/url_helper.php';
require_once SYSTEM_PATH . 'core/router.php';

// Register fallback autoloader (handles lowercase controller files)
spl_autoload_register('aurora_autoload');

function aurora_autoload($class) {
    // Convert namespace to path
    $class = str_replace('\\', '/', $class);

    // Handle AuraCore namespace mapping to core
    $class = str_replace('AuraCore/', 'core/', $class);
    $class = str_replace('SiteControllers/', 'controllers/', $class);
    // Lowercase first letter to match filesystem naming convention
    $class = lcfirst($class);

    // Check in site folder first
    $file = SITE_PATH . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Check in system folder
    $file = SYSTEM_PATH . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
}

// Load and initialize router
$router = require_once SYSTEM_PATH . 'config/routes.php';

// Dispatch the current request
if (!$router->dispatch()) {
    header("HTTP/1.0 404 Not Found");
    echo "404 - Route not found";
    exit;
}

ownstrap_css();
?>