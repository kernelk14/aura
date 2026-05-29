<?php
/**
 * Base Class
 * Provides common functionality for controllers and models
 */

namespace AuraCore;

class Base {
    /**
     * Load a view file
     * @param string $view View name (without .php)
     * @param array $data Data to pass to the view
     */
    protected function loadView($view, $data = []) {
        // Extract data to variables
        extract($data);
        
        $viewFile = SITE_PATH . 'views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "View not found: {$view}";
        }
    }

    /**
     * Load a model
     * @param string $model Model name (without .php)
     */
    protected function loadModel($model) {
        $modelFile = SITE_PATH . 'models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            $modelClass = ucfirst($model);
            return new $modelClass();
        }
        return null;
    }

    /**
     * Load the database library
     * @param string $group Configuration group name
     * @return Database Database instance
     */
    protected function loadDatabase($group = 'default') {
        static $db_instances = [];
        
        if (!isset($db_instances[$group])) {
            require_once SYSTEM_PATH . 'core/database.php';
            $config = $this->config('database.' . $group);
            $db_instances[$group] = new Database($config);
        }
        
        return $db_instances[$group];
    }

    /**
     * Redirect to a URL
     * @param string $url URL to redirect to
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }

    /**
     * Get a config item
     * @param string $item Config item name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    protected function config($item, $default = null) {
        static $config = [];
        
        if (empty($config)) {
            $configFile = SYSTEM_PATH . 'config/config.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
            }
            
            // Load database config separately
            $dbConfigFile = SYSTEM_PATH . 'config/database.php';
            if (file_exists($dbConfigFile)) {
                $dbConfig = require $dbConfigFile;
                $config['database'] = $dbConfig;
            }
        }
        
        // Handle database config access
        if (strpos($item, 'database.') === 0) {
            $parts = explode('.', $item, 2);
            if (isset($config['database'][$parts[1]])) {
                return $config['database'][$parts[1]] ?? $default;
            }
            return $default;
        }
        
        return $config[$item] ?? $default;
    }
}