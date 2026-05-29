<?php
/**
 * Router Class
 * Handles URL routing and dispatches requests to controllers
 */

namespace AuraCore;

class Router {
    private $routes = [];
    private $currentMethod = null;
    private $currentRoute = null;
    private $params = [];

    /**
     * Register a GET route
     * @param string $path Route path (e.g., 'users' or 'users/:id')
     * @param string|callable $handler Controller@method or callback
     */
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     * @param string $path Route path
     * @param string|callable $handler Controller@method or callback
     */
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route
     * @param string $path Route path
     * @param string|callable $handler Controller@method or callback
     */
    public function put($path, $handler) {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route
     * @param string $path Route path
     * @param string|callable $handler Controller@method or callback
     */
    public function delete($path, $handler) {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Register a route for any HTTP method
     * @param string $path Route path
     * @param string|callable $handler Controller@method or callback
     */
    public function any($path, $handler) {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
    }

    /**
     * Add a route to the routes array
     * @param string $method HTTP method
     * @param string $path Route path
     * @param string|callable $handler Handler
     */
    private function addRoute($method, $path, $handler) {
        $pattern = $this->pathToRegex($path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    /**
     * Convert path to regex pattern
     * Converts 'users/:id' to regex pattern and stores param names
     * @param string $path Route path
     * @return string Regex pattern
     */
    private function pathToRegex($path) {
        $pattern = preg_replace_callback('/:([\w]+)/', function($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $path);
        
        return '^' . $pattern . '$';
    }

    /**
     * Dispatch the current request
     * @param string $uri Request URI
     * @param string $method HTTP method
     * @return bool True if route matched and executed
     */
    public function dispatch($uri = null, $method = null) {
        if ($uri === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = explode('/?', $uri)[0];
        }
        
        if ($method === null) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        $uri = trim($uri, '/');

        // Try to find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match('~' . $route['pattern'] . '~', $uri, $matches)) {
                // Extract named parameters
                $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->currentRoute = $route;
                $this->currentMethod = $method;

                return $this->executeHandler($route['handler']);
            }
        }

        return false;
    }

    /**
     * Execute a route handler
     * @param string|callable $handler Handler (Controller@method or callable)
     * @return bool True if handler executed
     */
    private function executeHandler($handler) {
        if (is_callable($handler)) {
            call_user_func_array($handler, array_values($this->params));
            return true;
        }

        // Handle Controller@method format
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
            
            $controllerFile = SITE_PATH . 'controllers/' . strtolower($controller) . '.php';
            if (!file_exists($controllerFile)) {
                return false;
            }

            require_once $controllerFile;
            $controllerClass = '\\SiteControllers\\' . ucfirst($controller);
            
            if (!class_exists($controllerClass)) {
                return false;
            }

            $instance = new $controllerClass();
            
            if (!method_exists($instance, $method)) {
                return false;
            }

            call_user_func_array([$instance, $method], array_values($this->params));
            return true;
        }

        return false;
    }

    /**
     * Get all registered routes
     * @return array Routes array
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Get current route parameters
     * @return array Route parameters
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * Get a specific route parameter
     * @param string $name Parameter name
     * @param mixed $default Default value
     * @return mixed Parameter value
     */
    public function getParam($name, $default = null) {
        return $this->params[$name] ?? $default;
    }
}
?>
