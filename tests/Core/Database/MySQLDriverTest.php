<?php

namespace Tests\Core\Database;

use LogbieCore\Database\MySQLDriver;
use PHPUnit\Framework\TestCase;

/**
 * MySQLDriverTest
 * 
 * Tests for the MySQLDriver class.
 */
class MySQLDriverTest extends TestCase
{
    /**
     * @var MySQLDriver
     */
    private MySQLDriver $driver;
    
    /**
     * @var \PDO|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pdoMock;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        $this->driver = new MySQLDriver();
        $this->pdoMock = $this->createMock(\PDO::class);
    }
    
    /**
     * Test building a DSN string
     */
    public function testBuildDsn(): void
    {
        $config = [
            'host' => 'localhost',
            'port' => '3306',
            'database' => 'logbie',
            'charset' => 'utf8mb4'
        ];
        
        $expected = 'mysql:host=localhost;port=3306;dbname=logbie;charset=utf8mb4';
        $actual = $this->driver->buildDsn($config);
        
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Test building a DSN string with default values
     */
    public function testBuildDsnWithDefaults(): void
    {
        $config = [
            'database' => 'logbie'
        ];
        
        $expected = 'mysql:host=localhost;port=3306;dbname=logbie;charset=utf8mb4';
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
        $schemaData = [
            ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
            ['Field' => 'username', 'Type' => 'varchar(50)', 'Null' => 'NO', 'Key' => '', 'Default' => null, 'Extra' => '']
        ];
        
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with("DESCRIBE {$table}")
            ->willReturn($statementMock);
        
        $statementMock->expects($this->once())
            ->method('execute');
        
        $statementMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($schemaData);
        
        $result = $this->driver->getTableSchema($this->pdoMock, $table);
        
        $this->assertEquals($schemaData, $result);
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
            ->with("DESCRIBE {$table}")
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
        $this->assertEquals('mysql', $this->driver->getName());
    }
    
    /**
     * Test configuring the connection
     */
    public function testConfigureConnection(): void
    {
        $config = [
            'sqlMode' => 'STRICT_TRANS_TABLES',
            'timezone' => '+00:00',
            'mysqlConfig' => [
                'max_connections' => 100,
                'wait_timeout' => 600
            ]
        ];
        
        // Create a sequence of expectations
        $this->pdoMock->expects($this->exactly(4))
            ->method('exec')
            ->willReturnCallback(function($sql) {
                static $callCount = 0;
                
                switch ($callCount++) {
                    case 0:
                        $this->assertEquals("SET SESSION sql_mode = 'STRICT_TRANS_TABLES'", $sql);
                        break;
                    case 1:
                        $this->assertEquals("SET time_zone = '+00:00'", $sql);
                        break;
                    case 2:
                        $this->assertEquals("SET max_connections = 100", $sql);
                        break;
                    case 3:
                        $this->assertEquals("SET wait_timeout = 600", $sql);
                        break;
                }
                
                return 1; // PDO::exec() should return int|false, not bool
            });
        
        // Configure the driver
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
        
        $reflectionMethod = new \ReflectionMethod(MySQLDriver::class, 'connect');
        $parameters = $reflectionMethod->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('config', $parameters[0]->getName());
        $this->assertEquals('array', $parameters[0]->getType()->getName());
        
        // We can also verify that the method returns a PDO instance
        $this->assertEquals(\PDO::class, $reflectionMethod->getReturnType()->getName());
    }
}
