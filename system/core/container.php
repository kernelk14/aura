<?php

namespace AuraCore;

class Container
{
    protected $bindings = [];
    protected $instances = [];

    public function bind($abstract, $concrete = null)
    {
        $this->bindings[$abstract] = $concrete ?: $abstract;
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bindings[$abstract] = $concrete ?: $abstract;
        $this->instances[$abstract] = null;
    }

    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
        return $instance;
    }

    public function make($abstract)
    {
        if (isset($this->instances[$abstract])) {
            if ($this->instances[$abstract] === null) {
                $this->instances[$abstract] = $this->build($this->bindings[$abstract] ?? $abstract);
            }
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;

        if ($concrete instanceof \Closure) {
            $object = $concrete($this);
        } else {
            $object = $this->build($concrete);
        }

        if (array_key_exists($abstract, $this->instances)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function build($class)
    {
        if ($class instanceof \Closure) {
            return $class($this);
        }

        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("Target [$class] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    public function call($callable, $parameters = [])
    {
        if (is_string($callable) && strpos($callable, '@') !== false) {
            list($class, $method) = explode('@', $callable);
            $instance = $this->make($class);
            $callable = [$instance, $method];
        }

        if (is_array($callable)) {
            $reflector = new \ReflectionMethod($callable[0], $callable[1]);
        } else {
            $reflector = new \ReflectionFunction($callable);
        }

        $args = [];

        foreach ($reflector->getParameters() as $param) {
            $type = $param->getType();

            if ($type && !$type->isBuiltin()) {
                $typeName = $type->getName();
                if (isset($parameters[$param->getName()])) {
                    $args[] = $parameters[$param->getName()];
                } else {
                    $args[] = $this->make($typeName);
                }
            } elseif (isset($parameters[$param->getName()])) {
                $args[] = $parameters[$param->getName()];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Unable to resolve parameter [{$param->getName()}]");
            }
        }

        return $reflector->invokeArgs($args);
    }

    public function has($abstract)
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    protected function resolveDependencies(array $parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \RuntimeException("Unresolvable dependency [{$parameter->getName()}]");
            }
        }

        return $dependencies;
    }
}
