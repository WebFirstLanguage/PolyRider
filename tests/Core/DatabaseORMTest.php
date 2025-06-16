<?php

namespace Tests\Core;

use LogbieCore\DatabaseORM;
use LogbieCore\Database\DatabaseDriverInterface;
use LogbieCore\Database\MySQLDriver;
use LogbieCore\Database\SQLiteDriver;
use PHPUnit\Framework\TestCase;

/**
 * DatabaseORMTest
 * 
 * Tests for the DatabaseORM class.
 */
class DatabaseORMTest extends TestCase
{
    /**
     * @var DatabaseDriverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $driverMock;
    
    /**
     * @var \PDO|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pdoMock;
    
    /**
     * @var \PDOStatement|\PHPUnit\Framework\MockObject\MockObject
     */
    private $statementMock;
    
    /**
     * @var DatabaseORM
     */
    private $orm;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        $this->driverMock = $this->createMock(DatabaseDriverInterface::class);
        $this->pdoMock = $this->createMock(\PDO::class);
        $this->statementMock = $this->createMock(\PDOStatement::class);
        
        // Configure the driver mock to return the PDO mock
        $this->driverMock->method('connect')
            ->willReturn($this->pdoMock);
        
        // Configure the driver mock to return the statement mock
        $this->driverMock->method('prepare')
            ->willReturn($this->statementMock);
        
        // Configure the driver mock to return a name
        $this->driverMock->method('getName')
            ->willReturn('test');
        
        // Create the ORM with the mocked driver
        $this->orm = DatabaseORM::withDriver($this->driverMock, []);
    }
    
    /**
     * Test constructor with default driver
     */
    public function testConstructorWithDefaultDriver(): void
    {
        // This test requires a bit more setup to mock the DatabaseDriverFactory
        // We'll use reflection to bypass the factory and inject our mocks
        
        $orm = new DatabaseORM(['driver' => 'test']);
        
        // Use reflection to check that the ORM has a driver and PDO instance
        $reflectionClass = new \ReflectionClass(DatabaseORM::class);
        
        $driverProperty = $reflectionClass->getProperty('driver');
        $driverProperty->setAccessible(true);
        
        $pdoProperty = $reflectionClass->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        
        $this->assertInstanceOf(DatabaseDriverInterface::class, $driverProperty->getValue($orm));
        $this->assertInstanceOf(\PDO::class, $pdoProperty->getValue($orm));
    }
    
    /**
     * Test withDriver static constructor
     */
    public function testWithDriver(): void
    {
        $config = ['database' => 'test_db'];
        
        $this->driverMock->expects($this->once())
            ->method('connect')
            ->with($config)
            ->willReturn($this->pdoMock);
        
        $orm = DatabaseORM::withDriver($this->driverMock, $config);
        
        $this->assertInstanceOf(DatabaseORM::class, $orm);
        
        // Use reflection to check that the driver and PDO were set correctly
        $reflectionClass = new \ReflectionClass(DatabaseORM::class);
        
        $driverProperty = $reflectionClass->getProperty('driver');
        $driverProperty->setAccessible(true);
        
        $pdoProperty = $reflectionClass->getProperty('pdo');
        $pdoProperty->setAccessible(true);
        
        $this->assertSame($this->driverMock, $driverProperty->getValue($orm));
        $this->assertSame($this->pdoMock, $pdoProperty->getValue($orm));
    }
    
    /**
     * Test create method
     */
    public function testCreate(): void
    {
        $table = 'users';
        $data = [
            'username' => 'john_doe',
            'email' => 'john@example.com'
        ];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(array_values($data));
        
        // Configure the driver mock to return a last insert ID
        $this->driverMock->expects($this->once())
            ->method('lastInsertId')
            ->with($this->pdoMock)
            ->willReturn('123');
        
        $result = $this->orm->create($table, $data);
        
        $this->assertEquals(123, $result);
    }
    
    /**
     * Test create method with empty data
     */
    public function testCreateWithEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->create('users', []);
    }
    
    /**
     * Test read method
     */
    public function testRead(): void
    {
        $table = 'users';
        $conditions = ['id' => 1];
        $columns = ['id', 'username', 'email'];
        $options = ['orderBy' => 'id', 'orderDirection' => 'DESC', 'limit' => 10, 'offset' => 0];
        
        $expectedData = [
            ['id' => 1, 'username' => 'john_doe', 'email' => 'john@example.com']
        ];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with([1, 10, 0]);
        
        $this->statementMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedData);
        
        $result = $this->orm->read($table, $conditions, $columns, $options);
        
        $this->assertEquals($expectedData, $result);
    }
    
    /**
     * Test read method with no conditions
     */
    public function testReadWithNoConditions(): void
    {
        $table = 'users';
        $expectedData = [
            ['id' => 1, 'username' => 'john_doe', 'email' => 'john@example.com'],
            ['id' => 2, 'username' => 'jane_doe', 'email' => 'jane@example.com']
        ];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with([]);
        
        $this->statementMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedData);
        
        $result = $this->orm->read($table);
        
        $this->assertEquals($expectedData, $result);
    }
    
    /**
     * Test update method
     */
    public function testUpdate(): void
    {
        $table = 'users';
        $data = ['email' => 'john.updated@example.com'];
        $conditions = ['id' => 1];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['john.updated@example.com', 1]);
        
        $this->statementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);
        
        $result = $this->orm->update($table, $data, $conditions);
        
        $this->assertEquals(1, $result);
    }
    
    /**
     * Test update method with empty data
     */
    public function testUpdateWithEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->update('users', [], ['id' => 1]);
    }
    
    /**
     * Test update method with empty conditions
     */
    public function testUpdateWithEmptyConditions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->update('users', ['email' => 'test@example.com'], []);
    }
    
    /**
     * Test delete method
     */
    public function testDelete(): void
    {
        $table = 'users';
        $conditions = ['id' => 1];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with([1]);
        
        $this->statementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);
        
        $result = $this->orm->delete($table, $conditions);
        
        $this->assertEquals(1, $result);
    }
    
    /**
     * Test delete method with empty conditions
     */
    public function testDeleteWithEmptyConditions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orm->delete('users', []);
    }
    
    /**
     * Test query method for SELECT
     */
    public function testQuerySelect(): void
    {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $params = [1];
        $expectedData = [
            ['id' => 1, 'username' => 'john_doe', 'email' => 'john@example.com']
        ];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with($params);
        
        $this->statementMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedData);
        
        $result = $this->orm->query($sql, $params);
        
        $this->assertEquals($expectedData, $result);
    }
    
    /**
     * Test query method for non-SELECT
     */
    public function testQueryNonSelect(): void
    {
        $sql = 'UPDATE users SET email = ? WHERE id = ?';
        $params = ['john.updated@example.com', 1];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with($params);
        
        $this->statementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);
        
        $result = $this->orm->query($sql, $params);
        
        $this->assertEquals(1, $result);
    }
    
    /**
     * Test getManyToMany method
     */
    public function testGetManyToMany(): void
    {
        $sourceTable = 'users';
        $targetTable = 'roles';
        $pivotTable = 'user_roles';
        $conditions = ['user_id' => 1];
        
        $expectedData = [
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'editor']
        ];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with([1]);
        
        $this->statementMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedData);
        
        $result = $this->orm->getManyToMany($sourceTable, $targetTable, $pivotTable, $conditions);
        
        $this->assertEquals($expectedData, $result);
    }
    
    /**
     * Test getTableSchema method
     */
    public function testGetTableSchema(): void
    {
        $table = 'users';
        $expectedSchema = [
            ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => null, 'Extra' => 'auto_increment'],
            ['Field' => 'username', 'Type' => 'varchar(50)', 'Null' => 'NO', 'Key' => '', 'Default' => null, 'Extra' => '']
        ];
        
        // Configure the driver mock
        $this->driverMock->expects($this->once())
            ->method('getTableSchema')
            ->with($this->pdoMock, $table)
            ->willReturn($expectedSchema);
        
        $result = $this->orm->getTableSchema($table);
        
        $this->assertEquals($expectedSchema, $result);
        
        // Call again to test caching
        $result2 = $this->orm->getTableSchema($table);
        
        $this->assertEquals($expectedSchema, $result2);
    }
    
    /**
     * Test transaction methods
     */
    public function testTransactionMethods(): void
    {
        // Configure the driver mock for beginTransaction
        $this->driverMock->expects($this->once())
            ->method('beginTransaction')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Configure the driver mock for commit
        $this->driverMock->expects($this->once())
            ->method('commit')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Begin a transaction
        $result1 = $this->orm->beginTransaction();
        $this->assertTrue($result1);
        
        // Begin a nested transaction (should not call driver again)
        $result2 = $this->orm->beginTransaction();
        $this->assertTrue($result2);
        
        // Commit the nested transaction (should not call driver)
        $result3 = $this->orm->commit();
        $this->assertTrue($result3);
        
        // Commit the outer transaction (should call driver)
        $result4 = $this->orm->commit();
        $this->assertTrue($result4);
    }
    
    /**
     * Test rollback method
     */
    public function testRollback(): void
    {
        // Configure the driver mock for beginTransaction
        $this->driverMock->expects($this->once())
            ->method('beginTransaction')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Configure the driver mock for rollback
        $this->driverMock->expects($this->once())
            ->method('rollback')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Begin a transaction
        $this->orm->beginTransaction();
        
        // Begin a nested transaction
        $this->orm->beginTransaction();
        
        // Rollback (should reset transaction level and call driver)
        $result = $this->orm->rollback();
        $this->assertTrue($result);
    }
    
    /**
     * Test commit with no active transaction
     */
    public function testCommitWithNoActiveTransaction(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->orm->commit();
    }
    
    /**
     * Test rollback with no active transaction
     */
    public function testRollbackWithNoActiveTransaction(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->orm->rollback();
    }
    
    /**
     * Test batchOperation method
     */
    public function testBatchOperation(): void
    {
        // Configure the driver mock for beginTransaction
        $this->driverMock->expects($this->once())
            ->method('beginTransaction')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Configure the driver mock for commit
        $this->driverMock->expects($this->once())
            ->method('commit')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Define a callback function
        $callback = function($db) {
            return 'result';
        };
        
        // Execute the batch operation
        $result = $this->orm->batchOperation($callback);
        
        $this->assertEquals('result', $result);
    }
    
    /**
     * Test batchOperation method with exception
     */
    public function testBatchOperationWithException(): void
    {
        // Configure the driver mock for beginTransaction
        $this->driverMock->expects($this->once())
            ->method('beginTransaction')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Configure the driver mock for rollback
        $this->driverMock->expects($this->once())
            ->method('rollback')
            ->with($this->pdoMock)
            ->willReturn(true);
        
        // Define a callback function that throws an exception
        $callback = function($db) {
            throw new \Exception('Test exception');
        };
        
        // Execute the batch operation and expect an exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');
        
        $this->orm->batchOperation($callback);
    }
    
    /**
     * Test batchCreate method
     */
    public function testBatchCreate(): void
    {
        $table = 'users';
        $columns = ['username', 'email'];
        $rows = [
            ['username' => 'user1', 'email' => 'user1@example.com'],
            ['username' => 'user2', 'email' => 'user2@example.com']
        ];
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with(['user1', 'user1@example.com', 'user2', 'user2@example.com']);
        
        $this->statementMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(2);
        
        $result = $this->orm->batchCreate($table, $columns, $rows);
        
        $this->assertEquals(2, $result);
    }
    
    /**
     * Test batchCreate method with empty rows
     */
    public function testBatchCreateWithEmptyRows(): void
    {
        $result = $this->orm->batchCreate('users', ['username', 'email'], []);
        
        $this->assertEquals(0, $result);
    }
    
    /**
     * Test clearStatementCache method
     */
    public function testClearStatementCache(): void
    {
        // First, execute a query to populate the cache
        $this->statementMock->method('execute')->willReturn(true);
        $this->statementMock->method('fetchAll')->willReturn([]);
        
        $this->orm->query('SELECT * FROM users');
        
        // Now clear the cache
        $this->orm->clearStatementCache();
        
        // Use reflection to check that the cache is empty
        $reflectionClass = new \ReflectionClass(DatabaseORM::class);
        $cacheProperty = $reflectionClass->getProperty('statementCache');
        $cacheProperty->setAccessible(true);
        
        $this->assertEmpty($cacheProperty->getValue($this->orm));
    }
    
    /**
     * Test clearSchemaCache method
     */
    public function testClearSchemaCache(): void
    {
        // First, get a table schema to populate the cache
        $this->driverMock->method('getTableSchema')->willReturn([]);
        
        $this->orm->getTableSchema('users');
        
        // Now clear the cache
        $this->orm->clearSchemaCache();
        
        // Use reflection to check that the cache is empty
        $reflectionClass = new \ReflectionClass(DatabaseORM::class);
        $cacheProperty = $reflectionClass->getProperty('schemaCache');
        $cacheProperty->setAccessible(true);
        
        $this->assertEmpty($cacheProperty->getValue($this->orm));
    }
    
    /**
     * Test getPdo method
     */
    public function testGetPdo(): void
    {
        $pdo = $this->orm->getPdo();
        
        $this->assertSame($this->pdoMock, $pdo);
    }
    
    /**
     * Test getDriver method
     */
    public function testGetDriver(): void
    {
        $driver = $this->orm->getDriver();
        
        $this->assertSame($this->driverMock, $driver);
    }
    
    /**
     * Test optimize method for SQLite
     */
    public function testOptimizeSQLite(): void
    {
        // Configure the driver mock to return 'sqlite'
        $this->driverMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('sqlite');
        
        // Configure the statement mock for the first query (ANALYZE)
        $statement1 = $this->createMock(\PDOStatement::class);
        $statement1->expects($this->once())
            ->method('execute')
            ->with([]);
        
        // Configure the statement mock for the second query (VACUUM)
        $statement2 = $this->createMock(\PDOStatement::class);
        $statement2->expects($this->once())
            ->method('execute')
            ->with([]);
        
        // Configure the driver mock to return different statements for each query
        $this->driverMock->expects($this->at(1))
            ->method('prepare')
            ->with($this->pdoMock, 'ANALYZE;')
            ->willReturn($statement1);
        
        $this->driverMock->expects($this->at(2))
            ->method('prepare')
            ->with($this->pdoMock, 'VACUUM;')
            ->willReturn($statement2);
        
        $this->orm->optimize();
    }
    
    /**
     * Test optimize method for MySQL
     */
    public function testOptimizeMySQL(): void
    {
        // Configure the driver mock to return 'mysql'
        $this->driverMock->expects($this->once())
            ->method('getName')
            ->willReturn('mysql');
        
        // Configure the statement mock
        $this->statementMock->expects($this->once())
            ->method('execute')
            ->with([$table]);
        
        $this->orm->optimize();
    }
}