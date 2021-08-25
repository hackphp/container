<?php

namespace Hackphp\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Hackphp\Container\Exceptions\BindingException;

trait Resolver
{
    /**
     * Resolve the entry.
     *
     * @param  string $name
     * @return mixed
     */
    protected function resolve(string $name)
    {
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $resolvedEntry = $this->getResolvedEntry($name);

        if ($this->isShared($name)) {
            $this->instances[$name] = $resolvedEntry;
        }

        return $resolvedEntry;
    }

    /**
     * Get the resolved entry.
     *
     * @param  string $name
     * @return mixed
     */
    protected function getResolvedEntry(string $name)
    {
        $entry = $this->bindings[$name]["entry"] ?? $name;

        if ($entry instanceof Closure || $entry === $name) {
            $resolved = $this->build($entry);
        } else {
            $resolved = $this->resolve($entry);
        }

        return $resolved;
    }

    /**
     * Build the entry.
     *
     * @param  Closure|string $entry
     * @return mixed
     */
    protected function build($entry)
    {
        if ($entry instanceof Closure) {
            return $entry();
        }

        return $this->autowire($entry);
    }

    /**
     * Resolve the entry dependencies.
     *
     * @param  string $entry
     * @return object
     */
    protected function autowire($entry)
    {
        try {
            $reflector = new ReflectionClass($entry);
        } catch (ReflectionException $e) {
            throw new BindingException("Target class [$entry] does not exists.", 0, $e);
        }

        if (!$reflector->isInstantiable()) {
            throw new BindingException("Target class [$entry] is not instantiable.", 0, $e);
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $entry;
        }

        $instances = $this->resolveDependencies(
            $constructor->getParameters()
        );

        return $reflector->newInstanceArgs($instances);
    }

    /**
     * Resolve the class constructor dependencies.
     *
     * @param  array $dependencies
     * @return array
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        /** @var ReflectionParameter $dependency */
        foreach ($dependencies as $dependency) {
            $type = $dependency->getType();
            $declaringClass = $dependency->getDeclaringClass()->getName();

            if ($type->isBuiltin() || !$type instanceof ReflectionNamedType) {
                throw new BindingException("Unresolvable dependency: resolving [$dependency] in class {$declaringClass}");
            }

            if ($this->isCircularDependency($declaringClass, $type)) {
                throw new BindingException("Circular dependency.");
            }

            $results[] = $this->resolve($type->getName());
        }

        return $results;
    }

    /**
     * Check if there exists circular dependency.
     *
     * @param  string $declaringClass
     * @param  ReflectionNamedType $type
     * @return bool
     */
    protected function isCircularDependency(string $declaringClass, ReflectionNamedType $type)
    {
        if ($declaringClass == $type->getName()) {
            return true;
        }

        $autowire = new Autowire($type->getName());

        return $autowire->constructHasType($declaringClass);
    }

    /**
     * Check if the entry is shared or not.
     *
     * @param  string $name
     * @return bool
     */
    protected function isShared(string $name): bool
    {
        return isset($this->bindings[$name]) && $this->bindings[$name]["shared"];
    }
}
