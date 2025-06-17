<?php

namespace Tests\Core\Database;

use LogbieCore\DatabaseORM;
use LogbieCore\Database\DatabaseDriverFactory;
use LogbieCore\Database\MySQLDriver;
use LogbieCore\Database\SQLiteDriver;
use LogbieCore\Database\MigrationManager;
use LogbieCore\Database\SchemaBuilder;
use LogbieCore\Database\Migration;
use PHPUnit\Framework\TestCase;

/**
 * Migration Test
 * 
 * Integration tests for the migration system.
 * Tests migration commands, Schema Builder, and migration tracking.
 * 
 * Note: These tests require actual database connections.
 * For SQLite, an in-memory database is used.
 * For MySQL, a test database must be available.
 * 
 * To skip MySQL tests if no database is available, set the
 * SKIP_MYSQL_TESTS environment variable to 'true'.
 */
class MigrationTest extends TestCase
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
     * @var string
     */
    private $testMigrationsDir;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        $this->skipMySQLTests = getenv('SKIP_MYSQL_TESTS') === 'true';
        
        $sqliteDriver = new SQLiteDriver();
        $sqliteConfig = DatabaseTestFixtures::getSQLiteConfig();
        $this->sqliteOrm = DatabaseORM::withDriver($sqliteDriver, $sqliteConfig);
        
        if (!$this->skipMySQLTests) {
            try {
                $mysqlDriver = new MySQLDriver();
                $mysqlConfig = DatabaseTestFixtures::getMySQLConfig();
                $this->mysqlOrm = DatabaseORM::withDriver($mysqlDriver, $mysqlConfig);
            } catch (\PDOException $e) {
                $this->skipMySQLTests = true;
                $this->mysqlOrm = null;
                echo "MySQL tests skipped: " . $e->getMessage() . PHP_EOL;
            }
        }
        
        $this->testMigrationsDir = sys_get_temp_dir() . '/test_migrations_' . uniqid();
        mkdir($this->testMigrationsDir, 0755, true);
    }
    
    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        if (is_dir($this->testMigrationsDir)) {
            $files = glob($this->testMigrationsDir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($this->testMigrationsDir);
        }
        
        try {
            $this->sqliteOrm->query('DROP TABLE IF EXISTS migrations');
            if (!$this->skipMySQLTests && $this->mysqlOrm !== null) {
                $this->mysqlOrm->query('DROP TABLE IF EXISTS migrations');
            }
        } catch (\Exception $e) {
        }
    }
    
    /**
     * Test MigrationManager with SQLite
     */
    public function testMigrationManagerSQLite(): void
    {
        $this->performMigrationManagerTests($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test MigrationManager with MySQL
     */
    public function testMigrationManagerMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performMigrationManagerTests($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Test Schema Builder with SQLite
     */
    public function testSchemaBuilderSQLite(): void
    {
        $this->performSchemaBuilderTests($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test Schema Builder with MySQL
     */
    public function testSchemaBuilderMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performSchemaBuilderTests($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Test Migration execution with SQLite
     */
    public function testMigrationExecutionSQLite(): void
    {
        $this->performMigrationExecutionTests($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test Migration execution with MySQL
     */
    public function testMigrationExecutionMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performMigrationExecutionTests($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Test Migration rollback with SQLite
     */
    public function testMigrationRollbackSQLite(): void
    {
        $this->performMigrationRollbackTests($this->sqliteOrm, 'SQLite');
    }
    
    /**
     * Test Migration rollback with MySQL
     */
    public function testMigrationRollbackMySQL(): void
    {
        if ($this->skipMySQLTests) {
            $this->markTestSkipped('MySQL tests are skipped');
        }
        
        $this->performMigrationRollbackTests($this->mysqlOrm, 'MySQL');
    }
    
    /**
     * Perform MigrationManager tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performMigrationManagerTests(DatabaseORM $orm, string $driver): void
    {
        $manager = new MigrationManager($orm);
        
        $manager->ensureMigrationsTableExists();
        
        $this->assertTrue($manager->hasTable('migrations'));
        
        $this->assertEquals([], $manager->getExecutedMigrations());
        $this->assertEquals(1, $manager->getNextBatchNumber());
        $this->assertNull($manager->getLatestBatchNumber());
        
        $manager->recordMigration('2024_01_01_000000_test_migration.php', 1);
        $manager->recordMigration('2024_01_01_000001_another_migration.php', 1);
        
        $executed = $manager->getExecutedMigrations();
        $this->assertCount(2, $executed);
        $this->assertEquals(2, $manager->getNextBatchNumber());
        $this->assertEquals(1, $manager->getLatestBatchNumber());
        
        $batch1 = $manager->getMigrationsByBatch(1);
        $this->assertCount(2, $batch1);
        
        $manager->removeMigration('2024_01_01_000000_test_migration.php');
        $executed = $manager->getExecutedMigrations();
        $this->assertCount(1, $executed);
        
        $this->assertFalse($manager->hasBeenExecuted('2024_01_01_000000_test_migration.php'));
        $this->assertTrue($manager->hasBeenExecuted('2024_01_01_000001_another_migration.php'));
    }
    
    /**
     * Perform Schema Builder tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performSchemaBuilderTests(DatabaseORM $orm, string $driver): void
    {
        $schema = new SchemaBuilder($orm);
        
        $schema->create('test_users', function($table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        $this->assertTrue($schema->hasTable('test_users'));
        
        $schema->table('test_users', function($table) {
            $table->addColumn('phone', 'string')->nullable();
        });
        
        $userId = $orm->create('test_users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'active' => true,
            'phone' => '123-456-7890',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => null
        ]);
        
        $this->assertIsNumeric($userId);
        $this->assertGreaterThan(0, $userId);
        
        $user = $orm->read('test_users', ['id' => $userId]);
        $this->assertCount(1, $user);
        $this->assertEquals('testuser', $user[0]['username']);
        $this->assertEquals('test@example.com', $user[0]['email']);
        
        $schema->drop('test_users');
        $this->assertFalse($schema->hasTable('test_users'));
    }
    
    /**
     * Perform Migration execution tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performMigrationExecutionTests(DatabaseORM $orm, string $driver): void
    {
        $migrationContent = $this->createTestMigrationContent('CreateTestTable');
        $migrationFile = '2024_01_01_000000_create_test_table.php';
        file_put_contents($this->testMigrationsDir . '/' . $migrationFile, $migrationContent);
        
        require_once $this->testMigrationsDir . '/' . $migrationFile;
        
        $migration = new \CreateTestTable($orm);
        
        $migration->up();
        
        $schema = new SchemaBuilder($orm);
        $this->assertTrue($schema->hasTable('test_table'));
        
        $migration->down();
        $this->assertFalse($schema->hasTable('test_table'));
    }
    
    /**
     * Perform Migration rollback tests
     * 
     * @param DatabaseORM $orm The ORM instance
     * @param string $driver The driver name for test output
     */
    private function performMigrationRollbackTests(DatabaseORM $orm, string $driver): void
    {
        $manager = new MigrationManager($orm);
        $manager->ensureMigrationsTableExists();
        
        $manager->recordMigration('2024_01_01_000000_migration1.php', 1);
        $manager->recordMigration('2024_01_01_000001_migration2.php', 1);
        $manager->recordMigration('2024_01_01_000002_migration3.php', 2);
        
        $latestBatch = $manager->getLatestBatchNumber();
        $this->assertEquals(2, $latestBatch);
        
        $batch2Migrations = $manager->getMigrationsByBatch(2);
        $this->assertCount(1, $batch2Migrations);
        $this->assertEquals('2024_01_01_000002_migration3.php', $batch2Migrations[0]['migration']);
        
        $manager->removeMigration('2024_01_01_000002_migration3.php');
        
        $newLatestBatch = $manager->getLatestBatchNumber();
        $this->assertEquals(1, $newLatestBatch);
        
        $batch1Migrations = $manager->getMigrationsByBatch(1);
        $this->assertCount(2, $batch1Migrations);
    }
    
    /**
     * Create test migration content
     * 
     * @param string $className The migration class name
     * @return string The migration file content
     */
    private function createTestMigrationContent(string $className): string
    {
        return <<<PHP
<?php

use LogbieCore\Database\Migration;

class {$className} extends Migration
{
    public function up(): void
    {
        \$this->schema->create('test_table', function(\$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
        });
    }
    
    public function down(): void
    {
        \$this->schema->dropIfExists('test_table');
    }
}
PHP;
    }
}
