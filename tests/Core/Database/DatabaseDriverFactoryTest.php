<?php

namespace Tests\Core\Database;

use LogbieCore\Database\DatabaseDriverFactory;
use LogbieCore\Database\DatabaseDriverInterface;
use LogbieCore\Database\MySQLDriver;
use LogbieCore\Database\SQLiteDriver;
use PHPUnit\Framework\TestCase;

/**
 * DatabaseDriverFactoryTest
 * 
 * Tests for the DatabaseDriverFactory class.
 */
class DatabaseDriverFactoryTest extends TestCase
{
    /**
     * Test creating a MySQL driver
     */
    public function testCreateMySQLDriver(): void
    {
        $driver = DatabaseDriverFactory::create('mysql');
        
        $this->assertInstanceOf(DatabaseDriverInterface::class, $driver);
        $this->assertInstanceOf(MySQLDriver::class, $driver);
        $this->assertEquals('mysql', $driver->getName());
    }
    
    /**
     * Test creating a SQLite driver
     */
    public function testCreateSQLiteDriver(): void
    {
        $driver = DatabaseDriverFactory::create('sqlite');
        
        $this->assertInstanceOf(DatabaseDriverInterface::class, $driver);
        $this->assertInstanceOf(SQLiteDriver::class, $driver);
        $this->assertEquals('sqlite', $driver->getName());
    }
    
    /**
     * Test creating a driver with case-insensitive name
     */
    public function testCreateDriverCaseInsensitive(): void
    {
        $driver1 = DatabaseDriverFactory::create('MySQL');
        $driver2 = DatabaseDriverFactory::create('SQLITE');
        
        $this->assertInstanceOf(MySQLDriver::class, $driver1);
        $this->assertInstanceOf(SQLiteDriver::class, $driver2);
    }
    
    /**
     * Test creating an unsupported driver
     */
    public function testCreateUnsupportedDriver(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DatabaseDriverFactory::create('unsupported');
    }
    
    /**
     * Test registering a custom driver
     */
    public function testRegisterDriver(): void
    {
        // Create a mock driver class
        $mockDriver = new class implements DatabaseDriverInterface {
            public function connect(array $config): \PDO { return new \PDO('sqlite::memory:'); }
            public function buildDsn(array $config): string { return 'mock:dsn'; }
            public function prepare(\PDO $pdo, string $sql): \PDOStatement { return $pdo->prepare($sql); }
            public function lastInsertId(\PDO $pdo, ?string $name = null): string { return '1'; }
            public function beginTransaction(\PDO $pdo): bool { return true; }
            public function commit(\PDO $pdo): bool { return true; }
            public function rollback(\PDO $pdo): bool { return true; }
            public function getTableSchema(\PDO $pdo, string $table): array { return []; }
            public function getName(): string { return 'mock'; }
            public function configureConnection(\PDO $pdo, array $config): void {}
        };
        
        // Register the mock driver
        DatabaseDriverFactory::registerDriver('mock', get_class($mockDriver));
        
        // Create a driver using the registered class
        $driver = DatabaseDriverFactory::create('mock');
        
        $this->assertInstanceOf(DatabaseDriverInterface::class, $driver);
        $this->assertEquals('mock', $driver->getName());
    }
    
    /**
     * Test registering a driver with a non-existent class
     */
    public function testRegisterDriverNonExistentClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DatabaseDriverFactory::registerDriver('invalid', 'NonExistentClass');
    }
    
    /**
     * Test registering a driver with a class that doesn't implement DatabaseDriverInterface
     */
    public function testRegisterDriverInvalidClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        DatabaseDriverFactory::registerDriver('invalid', \stdClass::class);
    }
    
    /**
     * Test getting supported drivers
     */
    public function testGetSupportedDrivers(): void
    {
        $drivers = DatabaseDriverFactory::getSupportedDrivers();
        
        $this->assertIsArray($drivers);
        $this->assertContains('mysql', $drivers);
        $this->assertContains('sqlite', $drivers);
    }
}