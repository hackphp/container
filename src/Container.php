<?php

namespace Hackphp\Container;

use Closure;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    use Resolver;

    /**
     * Container instance.
     *
     * @var static
     */
    protected static $instance;

    /**
     * The binded services.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * The shared services.
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * Enable/Disable autowiring.
     *
     * @var bool
     */
    protected bool $autowiring = true;

    /**
     * Prevent create new instance.
     */
    private function __construct()
    {
    }

    /**
     * Get the Container instance.
     *
     * @return static
     */
    public static function getInstance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @inheritDoc
     */
    public function get(string $id)
    {
        return $this->make($id);
    }

    /**
     * @inheritDoc
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    /**
     * Bind entry to the container.
     *
     * @param  string $name
     * @param  Closure|string|null $entry
     * @param  bool $shared
     * @return void
     */
    public function bind(string $name, $entry = null, bool $shared = false): void
    {
        $this->bindings[$name] = [
            "entry" => $entry,
            "shared" => $shared
        ];
    }

    /**
     * Buind entry to the container as singleton.
     *
     * @param  string $name
     * @param  Closure|string|null $entry
     * @return void
     */
    public function singleton(string $name, $entry = null): void
    {
        $this->bind($name, $entry, true);
    }

    /**
     * Bind entry to the container shared instances.
     *
     * @param  string $name
     * @param  mixed $entry
     * @return void
     */
    public function instance(string $name, $entry): void
    {
        $this->instances[$name] = $entry;
    }

    /**
     * Get the entry from the container.
     *
     * @param  string $name
     * @return mixed
     */
    public function make(string $name)
    {
        return $this->resolve($name);
    }
}
