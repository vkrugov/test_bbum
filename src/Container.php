<?php

declare(strict_types=1);

namespace App;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;

class Container
{
    /** @var array<string, callable> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    /**
     * @param string $abstract
     * @param callable $concrete
     */
    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * @template T of object
     * @param class-string<T> $abstract
     * @return T
     * @throws \RuntimeException
     */
    public function make(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            /** @var T */
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $instance = ($this->bindings[$abstract])($this);
            $this->instances[$abstract] = $instance;

            /** @var T */
            return $instance;
        }

        $instance = $this->build($abstract);
        $this->instances[$abstract] = $instance;

        return $instance;
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws \RuntimeException
     */
    private function build(string $class): object
    {
        try {
            $reflector = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new \RuntimeException("Cannot resolve class [{$class}]: " . $e->getMessage(), 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("Class [{$class}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                    continue;
                }
                throw new \RuntimeException(
                    "Cannot resolve parameter [{$param->getName()}] in [{$class}]."
                );
            }

            /** @var class-string<object> $typeName */
            $typeName = $type->getName();
            $dependencies[] = $this->make($typeName);
        }

        return $reflector->newInstanceArgs($dependencies);
    }
}
