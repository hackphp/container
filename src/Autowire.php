<?php

namespace Hackphp\Container;

use ReflectionClass;
use ReflectionNamedType;

class Autowire
{
    /**
     * @var ReflectionClass
     */
    private ReflectionClass $class;

    /**
     * Create new Autowire.
     *
     * @param string $className
     */
    public function __construct($className)
    {
        $this->class = new ReflectionClass($className);
    }

    /**
     * Check if the class constructor has the given paramter.
     *
     * @param  string $paramName
     * @return bool
     */
    public function constructHasType($typeName): bool
    {
        $constructor = $this->class->getConstructor();

        if (is_null($constructor)) {
            return false;
        }

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if (!$type instanceof ReflectionNamedType) {
                continue;
            }

            if ($typeName == $type->getName()) {
                return true;
            }
        }

        return false;
    }
}
