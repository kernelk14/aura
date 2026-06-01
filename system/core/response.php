<?php

namespace AuraCore;

class Response
{
    protected $statusCode = 200;
    protected $headers = [];
    protected $body;
    protected $sent = false;

    public function status($code)
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function body($content)
    {
        $this->body = $content;
        return $this;
    }

    public function json($data, $status = 200)
    {
        $this->statusCode = $status;
        $this->headers['Content-Type'] = 'application/json';
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $this;
    }

    public function redirect($url, $status = 302)
    {
        $this->statusCode = $status;
        $this->headers['Location'] = $url;
        return $this->send();
    }

    public function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->redirect($referer);
    }

    public function with($key, $value)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_flash'][$key] = $value;
        return $this;
    }

    public function withInput()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['_flash_input'] = $_POST;
        return $this;
    }

    public function send()
    {
        if ($this->sent) {
            return $this;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }

        if ($this->body !== null) {
            echo $this->body;
        }

        $this->sent = true;
        return $this;
    }

    public static function jsonResponse($data, $status = 200)
    {
        $instance = new static;
        return $instance->json($data, $status)->send();
    }
}
