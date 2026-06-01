<?php

namespace AuraCore;

class Event
{
    protected static $listeners = [];

    public static function listen($event, $callback)
    {
        static::$listeners[$event][] = $callback;
    }

    public static function dispatch($event, $payload = [])
    {
        if (!isset(static::$listeners[$event])) {
            return;
        }

        $payload = is_array($payload) ? $payload : [$payload];

        foreach (static::$listeners[$event] as $callback) {
            call_user_func_array($callback, $payload);
        }
    }

    public static function removeListener($event, $callback = null)
    {
        if (!isset(static::$listeners[$event])) {
            return;
        }

        if ($callback === null) {
            unset(static::$listeners[$event]);
            return;
        }

        static::$listeners[$event] = array_values(array_filter(
            static::$listeners[$event],
            function ($c) use ($callback) {
                return $c !== $callback;
            }
        ));
    }

    public static function getListeners($event = null)
    {
        if ($event === null) {
            return static::$listeners;
        }
        return static::$listeners[$event] ?? [];
    }

    public static function flush()
    {
        static::$listeners = [];
    }
}
