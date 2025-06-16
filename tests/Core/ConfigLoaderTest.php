<?php

namespace Tests\Core;

use LogbieCore\ConfigLoader;
use PHPUnit\Framework\TestCase;

/**
 * ConfigLoaderTest
 * 
 * Tests for the ConfigLoader class, focusing on database configuration.
 */
class ConfigLoaderTest extends TestCase
{
    /**
     * @var string
     */
    private $configDir;
    
    /**
     * @var ConfigLoader
     */
    private $configLoader;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        // Create a temporary directory for test configuration files
        $this->configDir = sys_get_temp_dir() . '/logbie_config_test_' . uniqid();
        mkdir($this->configDir);
        
        // Create the ConfigLoader instance
        $this->configLoader = new ConfigLoader($this->configDir);
        
        // Create a test database configuration file
        $this->createTestDatabaseConfig();
    }
    
    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        // Remove the test configuration file
        if (file_exists($this->configDir . '/database.php')) {
            unlink($this->configDir . '/database.php');
        }
        
        // Remove the test directory
        if (is_dir($this->configDir)) {
            rmdir($this->configDir);
        }
    }
    
    /**
     * Create a test database configuration file
     */
    private function createTestDatabaseConfig(): void
    {
        $config = <<<'PHP'
<?php

return [
    // Default database connection
    'default' => 'mysql',
    
    // Database connections
    'connections' => [
        // MySQL connection
        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'logbie_test',
            'username' => 'test_user',
            'password' => 'test_password',
            'charset' => 'utf8mb4',
            'persistent' => true,
            'buffered' => true,
            'timeout' => 5,
            'sqlMode' => 'STRICT_TRANS_TABLES',
            'timezone' => '+00:00'
        ],
        
        // SQLite connection
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreignKeys' => true,
            'journalMode' => 'WAL',
            'synchronous' => 'NORMAL',
            'cacheSize' => 2000,
            'tempStore' => 'MEMORY'
        ]
    ]
];
PHP;
        
        file_put_contents($this->configDir . '/database.php', $config);
    }
    
    /**
     * Test loading the database configuration
     */
    public function testLoadDatabaseConfig(): void
    {
        $config = $this->configLoader->load('database');
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('default', $config);
        $this->assertArrayHasKey('connections', $config);
        $this->assertEquals('mysql', $config['default']);
    }
    
    /**
     * Test getting a configuration value
     */
    public function testGetConfigValue(): void
    {
        $defaultConnection = $this->configLoader->get('database.default');
        $this->assertEquals('mysql', $defaultConnection);
        
        $host = $this->configLoader->get('database.connections.mysql.host');
        $this->assertEquals('localhost', $host);
        
        $sqliteDatabase = $this->configLoader->get('database.connections.sqlite.database');
        $this->assertEquals(':memory:', $sqliteDatabase);
    }
    
    /**
     * Test getting a configuration value with a default
     */
    public function testGetConfigValueWithDefault(): void
    {
        $nonExistentValue = $this->configLoader->get('database.non_existent', 'default_value');
        $this->assertEquals('default_value', $nonExistentValue);
    }
    
    /**
     * Test getting the default database configuration
     */
    public function testGetDatabaseConfig(): void
    {
        $config = $this->configLoader->getDatabaseConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('driver', $config);
        $this->assertEquals('mysql', $config['driver']);
        $this->assertEquals('localhost', $config['host']);
        $this->assertEquals('3306', $config['port']);
        $this->assertEquals('logbie_test', $config['database']);
    }
    
    /**
     * Test getting a specific database connection configuration
     */
    public function testGetDatabaseConnectionConfig(): void
    {
        $mysqlConfig = $this->configLoader->getDatabaseConnectionConfig('mysql');
        
        $this->assertIsArray($mysqlConfig);
        $this->assertEquals('mysql', $mysqlConfig['driver']);
        $this->assertEquals('localhost', $mysqlConfig['host']);
        
        $sqliteConfig = $this->configLoader->getDatabaseConnectionConfig('sqlite');
        
        $this->assertIsArray($sqliteConfig);
        $this->assertEquals('sqlite', $sqliteConfig['driver']);
        $this->assertEquals(':memory:', $sqliteConfig['database']);
    }
    
    /**
     * Test checking if a configuration key exists
     */
    public function testHasConfigKey(): void
    {
        $this->assertTrue($this->configLoader->has('database.default'));
        $this->assertTrue($this->configLoader->has('database.connections.mysql'));
        $this->assertTrue($this->configLoader->has('database.connections.sqlite'));
        $this->assertFalse($this->configLoader->has('database.non_existent'));
    }
    
    /**
     * Test setting a configuration value
     */
    public function testSetConfigValue(): void
    {
        // Set a new value
        $this->configLoader->set('database.default', 'sqlite');
        
        // Verify the value was set
        $this->assertEquals('sqlite', $this->configLoader->get('database.default'));
        
        // Set a nested value
        $this->configLoader->set('database.connections.mysql.host', '127.0.0.1');
        
        // Verify the nested value was set
        $this->assertEquals('127.0.0.1', $this->configLoader->get('database.connections.mysql.host'));
    }
    
    /**
     * Test loading a non-existent configuration file
     */
    public function testLoadNonExistentConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->configLoader->load('non_existent');
    }
    
    /**
     * Test getting a value from a non-existent configuration file with default
     */
    public function testGetFromNonExistentConfigWithDefault(): void
    {
        $value = $this->configLoader->get('non_existent.key', 'default_value');
        $this->assertEquals('default_value', $value);
    }
}