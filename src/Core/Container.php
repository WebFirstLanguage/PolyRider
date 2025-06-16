<?php

namespace LogbieCore;

/**
 * Container Class
 * 
 * A dependency injection container that manages service instances
 * and their dependencies throughout the application lifecycle.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class Container
{
    /**
     * Stored service instances and definitions
     * 
     * @var array<string, mixed>
     */
    private array $services = [];
    
    /**
     * Singleton instances of services
     * 
     * @var array<string, object>
     */
    private array $instances = [];
    
    /**
     * Register a service with the container
     * 
     * @param string $id Service identifier
     * @param mixed $concrete Service definition (callable or object)
     * @param bool $singleton Whether to treat this service as a singleton
     * @return self
     * @throws \InvalidArgumentException If the service ID is invalid
     */
    public function register(string $id, mixed $concrete, bool $singleton = true): self
    {
        if (empty($id)) {
            throw new \InvalidArgumentException('Service ID cannot be empty');
        }
        
        $this->services[$id] = [
            'concrete' => $concrete,
            'singleton' => $singleton
        ];
        
        // If an instance already exists and we're redefining, remove it
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }
        
        return $this;
    }
    
    /**
     * Get a service from the container
     * 
     * @param string $id Service identifier
     * @return mixed The resolved service
     * @throws \InvalidArgumentException If the service is not registered
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException("Service '{$id}' is not registered in the container");
        }
        
        // Return existing instance if it's a singleton and already instantiated
        if ($this->services[$id]['singleton'] && isset($this->instances[$id])) {
            return $this->instances[$id];
        }
        
        // Resolve the service
        $concrete = $this->services[$id]['concrete'];
        $instance = $this->resolve($concrete);
        
        // Store the instance if it's a singleton
        if ($this->services[$id]['singleton']) {
            $this->instances[$id] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * Check if a service is registered in the container
     * 
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
    
    /**
     * Resolve a service definition into an instance
     * 
     * @param mixed $concrete Service definition
     * @return mixed The resolved service
     */
    private function resolve(mixed $concrete): mixed
    {
        // If it's a closure or callable, execute it with the container as parameter
        if (is_callable($concrete) && !is_object($concrete)) {
            return $concrete($this);
        }
        
        // If it's a closure object, execute it with the container as parameter
        if (is_object($concrete) && $concrete instanceof \Closure) {
            return $concrete($this);
        }
        
        // If it's already an object instance, just return it
        if (is_object($concrete)) {
            return $concrete;
        }
        
        // If it's a class name, instantiate it
        if (is_string($concrete) && class_exists($concrete)) {
            return $this->buildInstance($concrete);
        }
        
        // Otherwise, just return the concrete value
        return $concrete;
    }
    
    /**
     * Check if a dependency type can be resolved as a class
     * 
     * @param \ReflectionType|null $type The reflection type
     * @return bool True if the type can be resolved as a class
     */
    private function canResolveClassDependency(?\ReflectionType $type): bool
    {
        if ($type === null) {
            return false;
        }
        
        if (!($type instanceof \ReflectionNamedType)) {
            return false;
        }
        
        if ($type->isBuiltin()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get the class name from a reflection type
     * 
     * @param \ReflectionType|null $type The reflection type
     * @return string|null The class name or null if not a class type
     */
    private function getClassNameFromType(?\ReflectionType $type): ?string
    {
        if (!$this->canResolveClassDependency($type)) {
            return null;
        }
        
        // At this point we know it's a ReflectionNamedType and not null
        /** @var \ReflectionNamedType $type */
        return $type->getName();
    }
    
    /**
     * Build an instance of a class with automatic dependency resolution
     * 
     * @param string $className The class name to instantiate
     * @return object The instantiated object
     * @throws \ReflectionException If the class doesn't exist
     */
    private function buildInstance(string $className): object
    {
        $reflector = new \ReflectionClass($className);
        
        // If the class cannot be instantiated, throw an exception
        if (!$reflector->isInstantiable()) {
            throw new \InvalidArgumentException("Class {$className} is not instantiable");
        }
        
        // Get the constructor
        $constructor = $reflector->getConstructor();
        
        // If there is no constructor, just return a new instance
        if (is_null($constructor)) {
            return new $className();
        }
        
        // Get constructor parameters
        $parameters = $constructor->getParameters();
        
        // If there are no parameters, just return a new instance
        if (count($parameters) === 0) {
            return new $className();
        }
        
        // Build the dependencies
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();
            
            // Skip non-class dependencies
            if ($dependency === null ||
                ($dependency instanceof \ReflectionNamedType && $dependency->isBuiltin()) ||
                !($dependency instanceof \ReflectionNamedType)) {
                // If the parameter has a default value, use it
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                
                throw new \InvalidArgumentException(
                    "Cannot resolve parameter '{$parameter->getName()}' for class {$className}"
                );
            }
            
            // Get the dependency class name
            $dependencyClassName = $this->getClassNameFromType($dependency);
            
            // Skip if we couldn't get a class name
            if ($dependencyClassName === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                
                throw new \InvalidArgumentException(
                    "Cannot resolve parameter '{$parameter->getName()}' for class {$className}"
                );
            }
            
            // Check if the dependency is registered in the container
            if ($this->has($dependencyClassName)) {
                $dependencies[] = $this->get($dependencyClassName);
                continue;
            }
            
            // Otherwise, try to build the dependency
            try {
                $dependencies[] = $this->buildInstance($dependencyClassName);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    "Cannot resolve dependency '{$dependencyClassName}' for class {$className}: " . $e->getMessage()
                );
            }
        }
        
        // Create a new instance with the resolved dependencies
        return $reflector->newInstanceArgs($dependencies);
    }
    
    /**
     * Remove a service from the container
     * 
     * @param string $id Service identifier
     * @return self
     */
    public function remove(string $id): self
    {
        if (isset($this->services[$id])) {
            unset($this->services[$id]);
        }
        
        if (isset($this->instances[$id])) {
            unset($this->instances[$id]);
        }
        
        return $this;
    }
    
    /**
     * Clear all services from the container
     * 
     * @return self
     */
    public function clear(): self
    {
        $this->services = [];
        $this->instances = [];
        
        return $this;
    }
}