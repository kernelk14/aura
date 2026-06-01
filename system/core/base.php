<?php

namespace AuraCore;

class Base
{
    protected function loadView($view, $data = [], $useLayout = true)
    {
        extract($data);

        $viewFile = SITE_PATH . 'views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            echo "View not found: {$view}";
            return;
        }

        ob_start();
        require $viewFile;
        $viewContent = ob_get_clean();

        if ($useLayout) {
            $layoutFile = SYSTEM_PATH . 'core/content-wrapper.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                echo $viewContent;
            }
        } else {
            echo $viewContent;
        }
    }

    protected function loadModel($model)
    {
        $modelFile = SITE_PATH . 'models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            $modelClass = ucfirst($model);
            return new $modelClass();
        }
        return null;
    }

    protected function loadDatabase($group = 'default')
    {
        static $db_instances = [];

        if (!isset($db_instances[$group])) {
            require_once SYSTEM_PATH . 'core/database.php';
            $config = $this->config('database.' . $group);
            $db_instances[$group] = new Database($config);
        }

        return $db_instances[$group];
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    protected function config($item, $default = null)
    {
        static $config = [];

        if (empty($config)) {
            $configFile = SYSTEM_PATH . 'config/config.php';
            if (file_exists($configFile)) {
                $config = require $configFile;
            }

            $dbConfigFile = SYSTEM_PATH . 'config/database.php';
            if (file_exists($dbConfigFile)) {
                $dbConfig = require $dbConfigFile;
                $config['database'] = $dbConfig;
            }

            $appConfigFile = SITE_PATH . 'config/app.php';
            if (file_exists($appConfigFile)) {
                $appConfig = require $appConfigFile;
                $config = array_merge($config, $appConfig);
            }
        }

        if (strpos($item, 'database.') === 0) {
            $parts = explode('.', $item, 2);
            if (isset($config['database'][$parts[1]])) {
                return $config['database'][$parts[1]] ?? $default;
            }
            return $default;
        }

        if (strpos($item, '.') !== false) {
            $parts = explode('.', $item);
            $value = $config;
            foreach ($parts as $part) {
                if (!isset($value[$part])) {
                    return $default;
                }
                $value = $value[$part];
            }
            return $value;
        }

        return $config[$item] ?? $default;
    }

    protected function container()
    {
        static $container = null;
        if ($container === null) {
            $container = new Container();
        }
        return $container;
    }

    protected function logger($channel = 'app')
    {
        return new Logger();
    }

    protected function event()
    {
        return new Event();
    }

    protected function dotenv()
    {
        return new DotEnv();
    }
}
