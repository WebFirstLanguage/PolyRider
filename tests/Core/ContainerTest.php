<?php

namespace Tests\Core;

use LogbieCore\Container;
use PHPUnit\Framework\TestCase;

/**
 * Container Test Class
 * 
 * Unit tests for the Container class.
 */
class ContainerTest extends TestCase
{
    /**
     * Test container registration and retrieval
     */
    public function testRegisterAndGet(): void
    {
        $container = new Container();
        
        // Register a simple value
        $container->register('config', ['debug' => true]);
        
        // Register a class instance
        $stdClass = new \stdClass();
        $stdClass->property = 'value';
        $container->register('service', $stdClass);
        
        // Register a factory function
        $container->register('factory', function (Container $container) {
            $obj = new \stdClass();
            $obj->config = $container->get('config');
            return $obj;
        });
        
        // Test retrieving a simple value
        $config = $container->get('config');
        $this->assertIsArray($config);
        $this->assertTrue($config['debug']);
        
        // Test retrieving a class instance
        $service = $container->get('service');
        $this->assertInstanceOf(\stdClass::class, $service);
        $this->assertEquals('value', $service->property);
        
        // Test retrieving a factory result
        $factory = $container->get('factory');
        $this->assertInstanceOf(\stdClass::class, $factory);
        $this->assertIsArray($factory->config);
        $this->assertTrue($factory->config['debug']);
    }
    
    /**
     * Test singleton behavior
     */
    public function testSingleton(): void
    {
        $container = new Container();
        
        // Register a service as a singleton
        $container->register('singleton', function () {
            return new \stdClass();
        }, true);
        
        // Register a service as a non-singleton
        $container->register('non_singleton', function () {
            return new \stdClass();
        }, false);
        
        // Get the singleton service twice
        $singleton1 = $container->get('singleton');
        $singleton2 = $container->get('singleton');
        
        // Get the non-singleton service twice
        $nonSingleton1 = $container->get('non_singleton');
        $nonSingleton2 = $container->get('non_singleton');
        
        // The singleton instances should be the same object
        $this->assertSame($singleton1, $singleton2);
        
        // The non-singleton instances should be different objects
        $this->assertNotSame($nonSingleton1, $nonSingleton2);
    }
    
    /**
     * Test automatic dependency resolution
     */
    public function testDependencyResolution(): void
    {
        $container = new Container();
        
        // Define a simple class with no dependencies
        $container->register('simple', new class {
            public function getName(): string
            {
                return 'Simple';
            }
        });
        
        // Define a class that depends on the simple class
        $container->register('complex', new class($container->get('simple')) {
            private $simple;
            
            public function __construct($simple)
            {
                $this->simple = $simple;
            }
            
            public function getSimpleName(): string
            {
                return $this->simple->getName();
            }
        });
        
        // Test that the dependency was resolved correctly
        $complex = $container->get('complex');
        $this->assertEquals('Simple', $complex->getSimpleName());
    }
    
    /**
     * Test has method
     */
    public function testHas(): void
    {
        $container = new Container();
        
        // Register a service
        $container->register('service', new \stdClass());
        
        // Test has method
        $this->assertTrue($container->has('service'));
        $this->assertFalse($container->has('non_existent'));
    }
    
    /**
     * Test remove method
     */
    public function testRemove(): void
    {
        $container = new Container();
        
        // Register a service
        $container->register('service', new \stdClass());
        
        // Verify it exists
        $this->assertTrue($container->has('service'));
        
        // Remove the service
        $container->remove('service');
        
        // Verify it no longer exists
        $this->assertFalse($container->has('service'));
    }
    
    /**
     * Test clear method
     */
    public function testClear(): void
    {
        $container = new Container();
        
        // Register multiple services
        $container->register('service1', new \stdClass());
        $container->register('service2', new \stdClass());
        
        // Verify they exist
        $this->assertTrue($container->has('service1'));
        $this->assertTrue($container->has('service2'));
        
        // Clear the container
        $container->clear();
        
        // Verify they no longer exist
        $this->assertFalse($container->has('service1'));
        $this->assertFalse($container->has('service2'));
    }
    
    /**
     * Test exception when getting a non-existent service
     */
    public function testGetNonExistentService(): void
    {
        $container = new Container();
        
        $this->expectException(\InvalidArgumentException::class);
        $container->get('non_existent');
    }
    
    /**
     * Test exception when registering with an empty ID
     */
    public function testRegisterEmptyId(): void
    {
        $container = new Container();
        
        $this->expectException(\InvalidArgumentException::class);
        $container->register('', new \stdClass());
    }
}