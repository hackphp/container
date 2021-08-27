<?php

namespace Hackphp\Tests\Container;

use PHPUnit\Framework\TestCase;
use Hackphp\Container\Container;

class ContainerTest extends TestCase
{
    /** @test */
    public function it_must_be_a_signleton()
    {
        $instance1 = Container::getInstance();
        $instance2 = Container::getInstance();

        $this->assertSame($instance1, $instance2);

        $this->expectErrorMessage("Call to private");
        new Container;
    }

    /** @test */
    public function it_can_provide_instructions_for_resolving_a_class()
    {
        $container = Container::getInstance();

        $container->bind(ApiHandler::class, fn () => new ApiHandler("123"));

        $apiHandler = $container->make(ApiHandler::class);
        $this->assertInstanceOf(ApiHandler::class, $apiHandler);

        // check if we get new instance each time
        $anotherApiHandler = $container->make(ApiHandler::class);
        $this->assertNotSame($anotherApiHandler, $apiHandler);
    }

    /** @test */
    public function it_can_use_string_for_the_binding_name()
    {
        $container = Container::getInstance();

        $container->bind("api", fn () => new ApiHandler("123"));

        $api = $container->make("api");
        $this->assertInstanceOf(ApiHandler::class, $api);
    }

    /** @test */
    public function it_can_use_interface_as_binding_name()
    {
        $container = Container::getInstance();

        $container->bind(ApiInterface::class, fn () => new ApiHandler("123"));

        $api = $container->make(ApiInterface::class);
        $this->assertInstanceOf(ApiHandler::class, $api);
    }

    /** @test */
    public function it_can_pass_the_entry_class_without_use_closure()
    {
        $container = Container::getInstance();

        $container->bind(ApiInterface::class, UserApiHandler::class);

        $api = $container->make(ApiInterface::class);

        $this->assertInstanceOf(UserApiHandler::class, $api);
    }

    /** @test */
    public function it_can_resolve_the_class_without_provide_instructions()
    {
        $container = Container::getInstance();

        $api = $container->make(UserApiHandler::class);

        $this->assertInstanceOf(UserApiHandler::class, $api);
    }

    /** @test */
    public function it_can_resolve_recursively()
    {
        $container = Container::getInstance();

        $container->bind(ApiInterface::class, ApiHandler::class);
        $container->bind(ApiHandler::class, fn () => new ApiHandler("123"));

        $api = $container->make(ApiInterface::class);
        $this->assertInstanceOf(ApiHandler::class, $api);
    }

    /** @test */
    public function it_can_bind_a_singleton()
    {
        $container = Container::getInstance();

        $container->singleton(ApiHandler::class, fn () => new ApiHandler("123"));

        $api1 = $container->make(ApiHandler::class);
        $api2 = $container->make(ApiHandler::class);

        $this->assertSame($api1, $api2);
        $this->assertInstanceOf(ApiHandler::class, $api1);
    }

    /** @test */
    public function it_can_bind_a_singleton_by_passing_the_instance()
    {
        $container = Container::getInstance();

        $instance = new UserApiHandler;
        $container->instance(UserApiHandler::class, $instance);
        $resolved = $container->make(UserApiHandler::class);

        $this->assertSame($instance, $resolved);
    }

    /** @test */
    public function it_can_bind_a_singleton_by_class_name_only()
    {
        $container = Container::getInstance();

        $container->singleton(UserApiHandler::class);

        $api1 = $container->make(UserApiHandler::class);
        $api2 = $container->make(UserApiHandler::class);

        $this->assertSame($api1, $api2);
        $this->assertInstanceOf(UserApiHandler::class, $api1);
    }

    /** @test */
    public function it_does_dependency_injection()
    {
        $container = Container::getInstance();

        $api = $container->make(Api::class);

        $this->assertInstanceOf(Api::class, $api);
    }

    /** @test */
    public function it_throw_exception_with_direct_circular_dependency()
    {
        $container = Container::getInstance();

        $this->expectErrorMessage("Circular dependency.");
        $container->make(Test::class);

        $this->expectErrorMessage("Circular dependency.");
        $container->make(A::class);
    }
}

interface ApiInterface
{
}

class ApiHandler implements ApiInterface
{
    public function __construct($clientId)
    {
        // 
    }
}

class UserApiHandler implements ApiInterface
{
}

class Api
{
    public function __construct(ApiHandler $handler)
    {
    }
}

// Circular dependency

class Test
{
    public function __construct(Test $test)
    {
    }
}

// Circular dependency

class A
{
    public function __construct(B $b)
    {
    }
}

class B
{
    public function __construct(C $a)
    {
    }
}

class C
{
    public function __construct(A $a)
    {
    }
}
