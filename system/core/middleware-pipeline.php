<?php

namespace AuraCore;

class MiddlewarePipeline
{
    protected $stack = [];

    public function add($middleware)
    {
        $this->stack[] = $middleware;
        return $this;
    }

    public function run($request, callable $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->stack),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    if (is_string($middleware)) {
                        if (!class_exists($middleware)) {
                            $middleware = 'SiteMiddleware\\' . $middleware;
                        }
                        $middleware = new $middleware;
                    }
                    return $middleware->handle($request, $next);
                };
            },
            $destination
        );

        return $pipeline($request);
    }

    public function setStack(array $stack)
    {
        $this->stack = $stack;
        return $this;
    }

    public function getStack()
    {
        return $this->stack;
    }

    public function isEmpty()
    {
        return empty($this->stack);
    }
}
