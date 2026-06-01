<?php

namespace AuraCore;

class Request
{
    protected $get;
    protected $post;
    protected $server;
    protected $files;
    protected $cookies;
    protected $body;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->body = file_get_contents('php://input');
    }

    public function input($key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($this->get, $this->post);
        }

        $value = $this->post[$key] ?? $this->get[$key] ?? $default;

        if ($value === null && $this->isJson()) {
            $data = json_decode($this->body, true);
            return $data[$key] ?? $default;
        }

        return $value;
    }

    public function all()
    {
        $data = array_merge($this->get, $this->post);
        if ($this->isJson()) {
            $json = json_decode($this->body, true);
            if ($json) {
                $data = array_merge($data, $json);
            }
        }
        return $data;
    }

    public function only(array $keys)
    {
        $data = $this->all();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $data[$key] ?? null;
        }
        return $result;
    }

    public function except(array $keys)
    {
        $data = $this->all();
        foreach ($keys as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    public function has($key)
    {
        return isset($this->post[$key]) || isset($this->get[$key]);
    }

    public function method()
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path()
    {
        $uri = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return '/' . trim($uri, '/');
    }

    public function url()
    {
        $scheme = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . $this->path();
    }

    public function ip()
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent()
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function ajax()
    {
        return (isset($this->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    public function isMethod($method)
    {
        return strtoupper($method) === $this->method();
    }

    public function isGet()
    {
        return $this->isMethod('GET');
    }

    public function isPost()
    {
        return $this->isMethod('POST');
    }

    public function isJson()
    {
        $contentType = $this->server['CONTENT_TYPE'] ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    public function file($key)
    {
        return $this->files[$key] ?? null;
    }

    public function hasFile($key)
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] !== UPLOAD_ERR_NO_FILE;
    }

    public function __get($key)
    {
        return $this->input($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }
}
