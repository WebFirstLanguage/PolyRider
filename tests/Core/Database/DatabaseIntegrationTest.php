<?php

namespace Tests\Core\Database;

use LogbieCore\DatabaseORM;
use LogbieCore\Database\DatabaseDriverFactory;
use LogbieCore\Database\MySQLDriver;
use LogbieCore\Database\SQLiteDriver;
use PHPUnit\Framework\TestCase;

/**
 * DatabaseIntegrationTest
 * 
 * Integration tests for the database components.
 * 
 * Note: These tests require actual database connections.
 * For SQLite, an in-memory database is used.
 * For MySQL, a test database must be available.
 * 
 * To skip MySQL tests if no database is available, set the
 * SKIP_MYSQL_TESTS environment variable to 'true'.
 */
class DatabaseIntegrationTest extends TestCase
{
    /**
     * @var DatabaseORM
     */
    private $sqliteOrm;
    
    /**
     * @var DatabaseORM|null
     */
    private $mysqlOrm = null;
    
    /**
     * @var bool
     */
    private $skipMySQLTests = false;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        // Check if MySQL tests should be skipped
        $this->skipMySQLTests = getenv('SKIP_MYSQL_TESTS') === 'true';
        
        // Set up SQLite database (in-memory)
        $sqliteDriver = new SQLiteDriver();
        $sqliteConfig = DatabaseTestFixtures::getSQLiteConfig();
        $this->sqliteOrm = DatabaseORM::withDriver($sqliteDriver, $sqliteConfig);
        
        // Create tables in SQLite
        $this->sqliteOrm->query(DatabaseTestFixtures::createUsersTableSQLite());
        $this->sqliteOrm->query(DatabaseTestFixtures::createRolesTableSQLite());
        $this->sqliteOrm->query(DatabaseTestFixtures::createUserRolesTableSQLite());
        
        // Set up MySQL database if not skipped
        if (!$this->skipMySQLTests) {
            try {
                $mysqlDriver = new MySQLDriver();
                $mysqlConfig = DatabaseTestFixtures::getMySQLConfig();
                $this->mysqlOrm = DatabaseORM::withDriver($mysqlDriver, $mysqlConfig);
                
                // Create tables in MySQL
                $this->mysqlOrm->query(DatabaseTestFixtures::createUsersTableMySQL());
                $this->mysqlOrm->query(DatabaseTestFixtures::createRolesTableMySQL());
                $this->mysqlOrm->query(DatabaseTestFixtures::createUserRolesTableMySQL());
            } catch (\Exception $e) {
                // If connection fails, skip MySQL tests
                $this->skipMySQLTests = true;
                $this->mysqlOrm = null;
                echo "MySQL tests skipped: " . $e->getMessage() . PHP_EOL;
            }
        }
    }
    
    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        // Drop tables in SQLite
        foreach (DatabaseTestFixtures::getDropTablesSql() as $sql) {
            $this->sqliteOrm->query($sql);
        }
        
        // Drop tables in MySQL if not skipped
        if (!$this->skipMySQLTests && $this->mysqlOrm !== null) {
            foreach (DatabaseTestFixtures::getDropTablesSql() as $sql) {
                $this->mysqlOrm->query($sql);
            }
        }
    }
    
    /**
     * Test CRUD operations with SQLite
     */
    public function testCrudOperationsSQLite(): void
    {
        $this->performCrudOperations($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test CRUD operations with MySQL
     */
    public function testCrudOperationsMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performCrudOperations($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Test transaction handling with SQLite
     */
    public function testTransactionHandlingSQLite(): void
    {
        $this->performTransactionTests($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test transaction handling with MySQL
     */
    public function testTransactionHandlingMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performTransactionTests($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Test many-to-many relationships with SQLite
     */
    public function testManyToManySQLite(): void
    {
        $this->performManyToManyTests($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test many-to-many relationships with MySQL
     */
    public function testManyToManyMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performManyToManyTests($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Test batch operations with SQLite
     */
    public function testBatchOperationsSQLite(): void
    {
        $this->performBatchOperationsTests($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test batch operations with MySQL
     */
    public function testBatchOperationsMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performBatchOperationsTests($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Test driver switching
     */
    public function testDriverSwitching(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        // Create a user in SQLite
        $userData = DatabaseTestFixtures::getSampleUsers()[0];
        $sqliteUserId = $this->sqliteOrm->create('users', $userData);
        
        // Create the same user in MySQL
        $mysqlUserId = $this->mysqlOrm->create('users', $userData);
        
        // Read the user from SQLite
        $sqliteUser = $this->sqliteOrm->read('users', ['id' => $sqliteUserId])[0];
        
        // Read the user from MySQL
        $mysqlUser = $this->mysqlOrm->read('users', ['id' => $mysqlUserId])[0];
        
        // Compare the data (excluding the ID which may be different)
        $this->assertEquals($sqliteUser['username'], $mysqlUser['username']);
        $this->assertEquals($sqliteUser['email'], $mysqlUser['email']);
        
        // Test that the driver names are different
        $this->assertEquals('sqlite', $this->sqliteOrm->getDriver()->getName());
        $this->assertEquals('mysql', $this->mysqlOrm->getDriver()->getName());
    }
    
    /**
     * Test factory-based driver creation
     */
    public function testFactoryDriverCreation(): void
    {
        // Create drivers using the factory
        $sqliteDriver = DatabaseDriverFactory::create('sqlite');
        $this->assertInstanceOf(SQLiteDriver::class, $sqliteDriver);
        
        $mysqlDriver = DatabaseDriverFactory::create('mysql');
        $this->assertInstanceOf(MySQLDriver::class, $mysqlDriver);
        
        // Create ORMs with the factory-created drivers
        $sqliteOrm = DatabaseORM::withDriver($sqliteDriver, DatabaseTestFixtures::getSQLiteConfig());
        $this->assertInstanceOf(DatabaseORM::class, $sqliteOrm);
        
        if (!$this->skipMySQLTests) {
            $mysqlOrm = DatabaseORM::withDriver($mysqlDriver, DatabaseTestFixtures::getMySQLConfig());
            $this->assertInstanceOf(DatabaseORM::class, $mysqlOrm);
        }
    }
    
    /**
     * Perform CRUD operations tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performCrudOperations(DatabaseORM $orm, string $driver): void
    {
        // Create a user
        $userData = DatabaseTestFixtures::getSampleUsers()[0];
        $userId = $orm->create('users', $userData);
        
        $this->assertIsNumeric($userId);
        $this->assertGreaterThan(0, $userId);
        
        // Read the user
        $user = $orm->read('users', ['id' => $userId]);
        
        $this->assertCount(1, $user);
        $this->assertEquals($userData['username'], $user[0]['username']);
        $this->assertEquals($userData['email'], $user[0]['email']);
        
        // Update the user
        $updatedEmail = 'updated.' . $userData['email'];
        $affected = $orm->update('users', ['email' => $updatedEmail], ['id' => $userId]);
        
        $this->assertEquals(1, $affected);
        
        // Read the updated user
        $updatedUser = $orm->read('users', ['id' => $userId]);
        
        $this->assertEquals($updatedEmail, $updatedUser[0]['email']);
        
        // Delete the user
        $affected = $orm->delete('users', ['id' => $userId]);
        
        $this->assertEquals(1, $affected);
        
        // Verify the user is deleted
        $deletedUser = $orm->read('users', ['id' => $userId]);
        
        $this->assertEmpty($deletedUser);
    }
    
    /**
     * Perform transaction tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performTransactionTests(DatabaseORM $orm, string $driver): void
    {
        // Test successful transaction
        $orm->beginTransaction();
        
        $userData = DatabaseTestFixtures::getSampleUsers()[0];
        $userId = $orm->create('users', $userData);
        
        $orm->commit();
        
        // Verify the user was created
        $user = $orm->read('users', ['id' => $userId]);
        $this->assertCount(1, $user);
        
        // Test transaction rollback
        $orm->beginTransaction();
        
        $userData2 = DatabaseTestFixtures::getSampleUsers()[1];
        $userId2 = $orm->create('users', $userData2);
        
        $orm->rollback();
        
        // Verify the user was not created
        $user2 = $orm->read('users', ['id' => $userId2]);
        $this->assertEmpty($user2);
        
        // Test nested transactions
        $orm->beginTransaction(); // Outer transaction
        
        $userData3 = DatabaseTestFixtures::getSampleUsers()[2];
        $userId3 = $orm->create('users', $userData3);
        
        $orm->beginTransaction(); // Inner transaction
        
        $updatedEmail = 'nested.' . $userData3['email'];
        $orm->update('users', ['email' => $updatedEmail], ['id' => $userId3]);
        
        $orm->commit(); // Commit inner transaction
        
        // At this point, changes are not yet committed to the database
        
        $orm->commit(); // Commit outer transaction
        
        // Verify the changes were committed
        $user3 = $orm->read('users', ['id' => $userId3]);
        $this->assertEquals($updatedEmail, $user3[0]['email']);
    }
    
    /**
     * Perform many-to-many relationship tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performManyToManyTests(DatabaseORM $orm, string $driver): void
    {
        // Create users
        $userIds = [];
        foreach (DatabaseTestFixtures::getSampleUsers() as $userData) {
            $userIds[] = $orm->create('users', $userData);
        }
        
        // Create roles
        $roleIds = [];
        foreach (DatabaseTestFixtures::getSampleRoles() as $roleData) {
            $roleIds[] = $orm->create('roles', $roleData);
        }
        
        // Create user-role relationships
        foreach (DatabaseTestFixtures::getSampleUserRoles() as $userRole) {
            $orm->create('user_roles', [
                'user_id' => $userIds[$userRole['user_id'] - 1],
                'role_id' => $roleIds[$userRole['role_id'] - 1]
            ]);
        }
        
        // Test getManyToMany
        $userRoles = $orm->getManyToMany('users', 'roles', 'user_roles', ['user_id' => $userIds[0]]);
        
        // User 1 (john_doe) should have 2 roles: admin and editor
        $this->assertCount(2, $userRoles);
        
        // Verify role names
        $roleNames = array_column($userRoles, 'name');
        $this->assertContains('admin', $roleNames);
        $this->assertContains('editor', $roleNames);
    }
    
    /**
     * Perform batch operations tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performBatchOperationsTests(DatabaseORM $orm, string $driver): void
    {
        // Test batchOperation
        $result = $orm->batchOperation(function($db) {
            $userIds = [];
            
            // Create multiple users in a transaction
            foreach (DatabaseTestFixtures::getSampleUsers() as $userData) {
                $userIds[] = $db->create('users', $userData);
            }
            
            return $userIds;
        });
        
        // Verify users were created
        $this->assertCount(3, $result);
        
        $users = $orm->read('users');
        $this->assertCount(3, $users);
        
        // Test batchCreate
        $orm->query(DatabaseTestFixtures::getDropTablesSql()[0]); // Drop user_roles
        $orm->query(DatabaseTestFixtures::getDropTablesSql()[1]); // Drop roles
        $orm->query(DatabaseTestFixtures::getDropTablesSql()[2]); // Drop users
        
        if ($driver === 'SQLite') {
            $orm->query(DatabaseTestFixtures::createUsersTableSQLite());
        } else {
            $orm->query(DatabaseTestFixtures::createUsersTableMySQL());
        }
        
        $columns = ['username', 'email', 'created_at'];
        $rows = [];
        
        foreach (DatabaseTestFixtures::getSampleUsers() as $userData) {
            $rows[] = [
                'username' => $userData['username'],
                'email' => $userData['email'],
                'created_at' => $userData['created_at']
            ];
        }
        
        $affected = $orm->batchCreate('users', $columns, $rows);
        
        $this->assertEquals(3, $affected);
        
        $users = $orm->read('users');
        $this->assertCount(3, $users);
    }
}
