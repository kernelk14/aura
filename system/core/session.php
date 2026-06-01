<?php

namespace AuraCore;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
        return $this;
    }

    public function all()
    {
        return $_SESSION ?? [];
    }

    public function destroy()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public function regenerate()
    {
        session_regenerate_id(true);
        return $this;
    }

    public function flash($key, $value = null)
    {
        if ($value === null) {
            $val = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $val;
        }

        $_SESSION['_flash'][$key] = $value;
        return $this;
    }

    public function hasFlash($key)
    {
        return isset($_SESSION['_flash'][$key]);
    }

    public function flashInput($key = null)
    {
        if ($key === null) {
            $input = $_SESSION['_flash_input'] ?? [];
            unset($_SESSION['_flash_input']);
            return $input;
        }

        $value = $_SESSION['_flash_input'][$key] ?? null;
        return $value;
    }

    public function reflash()
    {
        if (isset($_SESSION['_flash'])) {
            // Keep flash data for another request
        }
        return $this;
    }

    public function keep($keys)
    {
        $keys = is_array($keys) ? $keys : [$keys];
        // Flash data persists if we don't clear it — it's cleared on read
        // So we just mark it to not be cleared
        foreach ($keys as $key) {
            if (isset($_SESSION['_flash'][$key])) {
                $_SESSION['_keep'][] = $key;
            }
        }
        return $this;
    }
}
