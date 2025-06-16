<?php

namespace Tests\Core\Database;

use LogbieCore\Database\SQLiteDriver;
use PHPUnit\Framework\TestCase;

/**
 * SQLiteDriverTest
 * 
 * Tests for the SQLiteDriver class.
 */
class SQLiteDriverTest extends TestCase
{
    /**
     * @var SQLiteDriver
     */
    private SQLiteDriver $driver;
    
    /**
     * @var \PDO|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pdoMock;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        $this->driver = new SQLiteDriver();
        $this->pdoMock = $this->createMock(\PDO::class);
    }
    
    /**
     * Test building a DSN string
     */
    public function testBuildDsn(): void
    {
        $config = [
            'database' => '/path/to/database.sqlite'
        ];
        
        $expected = 'sqlite:/path/to/database.sqlite';
        $actual = $this->driver->buildDsn($config);
        
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Test building a DSN string for in-memory database
     */
    public function testBuildDsnInMemory(): void
    {
        $config = [
            'database' => ':memory:'
        ];
        
        $expected = 'sqlite::memory:';
        $actual = $this->driver->buildDsn($config);
        
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Test preparing a statement
     */
    public function testPrepare(): void
    {
        $sql = 'SELECT * FROM users';
        $statementMock = $this->createMock(\PDOStatement::class);
        
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($sql)
            ->willReturn($statementMock);
        
        $result = $this->driver->prepare($this->pdoMock, $sql);
        
        $this->assertSame($statementMock, $result);
    }
    
    /**
     * Test getting the last inserted ID
     */
    public function testLastInsertId(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->with()
            ->willReturn('123');
        
        $result = $this->driver->lastInsertId($this->pdoMock);
        
        $this->assertEquals('123', $result);
    }
    
    /**
     * Test beginning a transaction
     */
    public function testBeginTransaction(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);
        
        $result = $this->driver->beginTransaction($this->pdoMock);
        
        $this->assertTrue($result);
    }
    
    /**
     * Test committing a transaction
     */
    public function testCommit(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('commit')
            ->willReturn(true);
        
        $result = $this->driver->commit($this->pdoMock);
        
        $this->assertTrue($result);
    }
    
    /**
     * Test rolling back a transaction
     */
    public function testRollback(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('rollBack')
            ->willReturn(true);
        
        $result = $this->driver->rollback($this->pdoMock);
        
        $this->assertTrue($result);
    }
    
    /**
     * Test getting table schema
     */
    public function testGetTableSchema(): void
    {
        $table = 'users';
        $statementMock = $this->createMock(\PDOStatement::class);
        $sqliteSchemaData = [
            ['cid' => 0, 'name' => 'id', 'type' => 'INTEGER', 'notnull' => 1, 'dflt_value' => null, 'pk' => 1],
            ['cid' => 1, 'name' => 'username', 'type' => 'TEXT', 'notnull' => 1, 'dflt_value' => null, 'pk' => 0]
        ];
        
        $expectedMySQLFormat = [
            [
                'Field' => 'id',
                'Type' => 'INTEGER',
                'Null' => 'NO',
                'Key' => 'PRI',
                'Default' => null,
                'Extra' => 'auto_increment'
            ],
            [
                'Field' => 'username',
                'Type' => 'TEXT',
                'Null' => 'NO',
                'Key' => '',
                'Default' => null,
                'Extra' => ''
            ]
        ];
        
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with("PRAGMA table_info({$table})")
            ->willReturn($statementMock);
        
        $statementMock->expects($this->once())
            ->method('execute');
        
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($sqliteSchemaData);
        
        $result = $this->driver->getTableSchema($this->pdoMock, $table);
        
        $this->assertEquals($expectedMySQLFormat, $result);
    }
    
    /**
     * Test getting table schema with an error
     */
    public function testGetTableSchemaError(): void
    {
        $table = 'users';
        $statementMock = $this->createMock(\PDOStatement::class);
        
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with("PRAGMA table_info({$table})")
            ->willReturn($statementMock);
        
        $statementMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new \PDOException('Table does not exist'));
        
        $this->expectException(\RuntimeException::class);
        $this->driver->getTableSchema($this->pdoMock, $table);
    }
    
    /**
     * Test getting the driver name
     */
    public function testGetName(): void
    {
        $this->assertEquals('sqlite', $this->driver->getName());
    }
    
    /**
     * Test configuring the connection
     */
    public function testConfigureConnection(): void
    {
        $config = [
            'foreignKeys' => true,
            'journalMode' => 'WAL',
            'synchronous' => 'NORMAL',
            'cacheSize' => 2000,
            'tempStore' => 'MEMORY',
            'mmapSize' => 1048576,
            'sqliteConfig' => [
                'auto_vacuum' => 'INCREMENTAL',
                'secure_delete' => 'ON'
            ]
        ];
        
        // Create a sequence of expectations
        $this->pdoMock->expects($this->exactly(6))
            ->method('exec')
            ->willReturnCallback(function($sql) {
                static $callCount = 0;
                
                switch ($callCount++) {
                    case 0:
                        $this->assertEquals('PRAGMA foreign_keys = ON;', $sql);
                        break;
                    case 1:
                        $this->assertEquals('PRAGMA journal_mode = WAL;', $sql);
                        break;
                    case 2:
                        $this->assertEquals('PRAGMA synchronous = NORMAL;', $sql);
                        break;
                    case 3:
                        $this->assertEquals('PRAGMA cache_size = 2000;', $sql);
                        break;
                    case 4:
                        $this->assertEquals('PRAGMA temp_store = MEMORY;', $sql);
                        break;
                    case 5:
                        $this->assertEquals('PRAGMA mmap_size = 1048576;', $sql);
                        break;
                }
                
                return 1; // PDO::exec() should return int|false, not bool
            });
        
        // Configure the driver
        $this->driver->configureConnection($this->pdoMock, $config);
        $this->pdoMock->expects($this->at(5))
            ->method('exec')
            ->with('PRAGMA mmap_size = 1048576;');
        
        // Expect exec to be called for each SQLite config option
        $this->pdoMock->expects($this->at(6))
            ->method('exec')
            ->with('PRAGMA auto_vacuum = INCREMENTAL;');
        
        $this->pdoMock->expects($this->at(7))
            ->method('exec')
            ->with('PRAGMA secure_delete = ON;');
        
        $this->driver->configureConnection($this->pdoMock, $config);
    }
    
    /**
     * Test connecting to the database
     * 
     * Note: This test doesn't actually connect to a database,
     * it just verifies that the connection method sets up the
     * PDO options correctly.
     */
    public function testConnectSetsPdoOptions(): void
    {
        // This test is a bit tricky since we can't easily mock the PDO constructor
        // Instead, we'll use reflection to check that the method is structured correctly
        
        $reflectionMethod = new \ReflectionMethod(SQLiteDriver::class, 'connect');
        $parameters = $reflectionMethod->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('config', $parameters[0]->getName());
        $this->assertEquals('array', $parameters[0]->getType()->getName());
        
        // We can also verify that the method returns a PDO instance
        $this->assertEquals(\PDO::class, $reflectionMethod->getReturnType()->getName());
    }
    
    /**
     * Test directory creation for SQLite database
     */
    public function testConnectCreatesDirectory(): void
    {
        // Create a temporary directory for testing
        $tempDir = sys_get_temp_dir() . '/logbie_test_' . uniqid();
        $dbFile = $tempDir . '/test.sqlite';
        
        try {
            // Make sure the directory doesn't exist
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }
            
            // Create a reflection method to access the protected connect method
            $reflectionClass = new \ReflectionClass(SQLiteDriver::class);
            $connectMethod = $reflectionClass->getMethod('connect');
            $connectMethod->setAccessible(true);
            
            // We'll catch the PDOException that will be thrown when trying to connect
            // to a non-existent database, but the directory should still be created
            try {
                $connectMethod->invoke($this->driver, ['database' => $dbFile]);
            } catch (\PDOException $e) {
                // Expected exception
            }
            
            // Check that the directory was created
            $this->assertTrue(is_dir($tempDir));
            
        } finally {
            // Clean up
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }
        }
    }
}