<?php

namespace AuraCore;

class Router
{
    private $routes = [];
    private $currentMethod = null;
    private $currentRoute = null;
    private $params = [];
    private $groupStack = [];
    private $globalMiddleware = [];

    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function any($path, $handler)
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
    }

    public function match(array $methods, $path, $handler)
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler);
        }
    }

    public function middleware($middleware)
    {
        if (!empty($this->groupStack)) {
            $this->groupStack[count($this->groupStack) - 1]['middleware'][] = $middleware;
        } else {
            $this->globalMiddleware[] = $middleware;
        }
        return $this;
    }

    public function group(array $attributes, \Closure $callback)
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
        return $this;
    }

    private function addRoute($method, $path, $handler)
    {
        $pattern = $this->pathToRegex($path);
        $middleware = $this->globalMiddleware;

        if (!empty($this->groupStack)) {
            $group = end($this->groupStack);
            if (isset($group['prefix'])) {
                $path = trim($group['prefix'], '/') . '/' . trim($path, '/');
                $path = trim($path, '/');
                $pattern = $this->pathToRegex($path);
            }
            if (isset($group['middleware'])) {
                $middleware = array_merge($middleware, $group['middleware']);
            }
            if (isset($group['namespace'])) {
                if (is_string($handler) && strpos($handler, '@') !== false) {
                    list($controller, $method) = explode('@', $handler);
                    $handler = $group['namespace'] . '\\' . $controller . '@' . $method;
                }
            }
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private function pathToRegex($path)
    {
        $pattern = preg_replace_callback('/:([\w]+)/', function ($matches) {
            return '(?P<' . $matches[1] . '>[^/]+)';
        }, $path);

        return '^' . $pattern . '$';
    }

    public function dispatch($uri = null, $method = null)
    {
        if ($uri === null) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = explode('/?', $uri)[0];
        }

        if ($method === null) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        $uri = trim($uri, '/');

        $request = $this->createRequest();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match('~' . $route['pattern'] . '~', $uri, $matches)) {
                $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->currentRoute = $route;
                $this->currentMethod = $method;

                return $this->runRoute($route, $request);
            }
        }

        return false;
    }

    protected function runRoute($route, $request)
    {
        $handler = $route['handler'];
        $middleware = $route['middleware'] ?? [];

        $pipeline = new MiddlewarePipeline();

        foreach ($middleware as $mw) {
            if (is_string($mw)) {
                $fullClass = class_exists($mw) ? $mw : 'SiteMiddleware\\' . $mw;
                if (class_exists($fullClass)) {
                    $mw = new $fullClass;
                }
            }
            $pipeline->add($mw);
        }

        $destination = function ($request) use ($handler) {
            return $this->executeHandler($handler);
        };

        return $pipeline->run($request, $destination);
    }

    protected function createRequest()
    {
        if (class_exists('AuraCore\\Request')) {
            return new Request();
        }
        return null;
    }

    private function executeHandler($handler)
    {
        $params = array_values($this->params);

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return true;
        }

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

            if (method_exists($instance, 'setRequest')) {
                $instance->setRequest($this->createRequest());
            }

            if (!method_exists($instance, $method)) {
                return false;
            }

            call_user_func_array([$instance, $method], $params);
            return true;
        }

        return false;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }
}
