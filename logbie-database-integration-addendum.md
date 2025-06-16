# Logbie Framework: Database Integration Strategy - Addendum

This document provides additional details on performance considerations and migration strategies for the Logbie Framework's database integration plan, focusing on MySQL and SQLite support.

## 1. Performance Considerations and Optimization Strategies

### 1.1 Comparative Performance Analysis

| Aspect | MySQL | SQLite | Optimization Approach |
|--------|-------|--------|----------------------|
| **Query Speed** | Faster for complex queries and large datasets | Faster for simple queries and small-to-medium datasets | Use appropriate DB for workload type |
| **Concurrency** | High (100+ concurrent connections) | Limited (1 writer, multiple readers) | Implement connection pooling for MySQL; use WAL mode for SQLite |
| **Memory Usage** | 50-500MB baseline + per-connection overhead | 1-10MB total | Configure MySQL buffer pools based on available memory; keep SQLite page cache optimized |
| **Disk I/O** | Higher write throughput | Lower write throughput | Batch operations for SQLite; optimize MySQL InnoDB settings |
| **CPU Usage** | Higher for complex operations | Lower overall | Optimize query complexity for MySQL; use indexes effectively for both |

### 1.2 MySQL Performance Optimization

#### 1.2.1 Configuration Parameters

| Parameter | Recommended Setting | Impact |
|-----------|---------------------|--------|
| `innodb_buffer_pool_size` | 50-70% of available RAM | Caches table and index data in memory |
| `innodb_log_file_size` | 256MB - 1GB | Larger log files reduce disk I/O |
| `innodb_flush_log_at_trx_commit` | 1 (default) or 2 (performance) | Controls durability vs. performance |
| `max_connections` | 100-500 based on workload | Limits concurrent connections |
| `query_cache_size` | 0 (disable) for MySQL 5.7+, 50-100MB for older versions | Query caching can help for read-heavy workloads |
| `tmp_table_size` | 64-256MB | Affects temporary table performance |

#### 1.2.2 Connection Management

```php
// Implementation for MySQLDriver
public function connect(array $config): \PDO
{
    // Set persistent connections for better performance
    $options = $config['options'] ?? [];
    $options[\PDO::ATTR_PERSISTENT] = $config['persistent'] ?? true;
    
    // Disable prepared statement emulation for better security and performance
    $options[\PDO::ATTR_EMULATE_PREPARES] = false;
    
    // Use buffered queries by default
    $options[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = 
        $config['buffered'] ?? true;
    
    // Set connection timeout
    $options[\PDO::ATTR_TIMEOUT] = $config['timeout'] ?? 5;
    
    // Create PDO instance with optimized options
    return new \PDO(
        $this->buildDsn($config),
        $config['username'],
        $config['password'],
        $options
    );
}
```

#### 1.2.3 Query Optimization

- **Indexing Strategy**: Create indexes on frequently queried columns and join conditions
- **Query Analysis**: Implement query logging with execution time for slow queries
- **Prepared Statement Caching**: Enhance current caching mechanism with statement reuse

```php
// Enhanced statement caching
private function prepareWithCache(string $sql): \PDOStatement
{
    $cacheKey = md5($sql);
    
    if (isset($this->statementCache[$cacheKey])) {
        // Reset the statement for reuse
        $this->statementCache[$cacheKey]->closeCursor();
        return $this->statementCache[$cacheKey];
    }
    
    $statement = $this->pdo->prepare($sql);
    $this->statementCache[$cacheKey] = $statement;
    
    return $statement;
}
```

#### 1.2.4 Batch Operations

For bulk inserts or updates, use multi-row operations:

```php
// Batch insert implementation
public function batchCreate(string $table, array $columns, array $rows): int
{
    if (empty($rows)) {
        return 0;
    }
    
    $placeholders = [];
    $values = [];
    
    foreach ($rows as $row) {
        $rowPlaceholders = [];
        
        foreach ($columns as $column) {
            $rowPlaceholders[] = '?';
            $values[] = $row[$column] ?? null;
        }
        
        $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
    }
    
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES %s',
        $table,
        implode(', ', $columns),
        implode(', ', $placeholders)
    );
    
    $statement = $this->prepare($sql);
    $statement->execute($values);
    
    return $statement->rowCount();
}
```

### 1.3 SQLite Performance Optimization

#### 1.3.1 Configuration Parameters

| Parameter | Recommended Setting | Impact |
|-----------|---------------------|--------|
| `journal_mode` | WAL (Write-Ahead Logging) | Improves concurrency and write performance |
| `synchronous` | NORMAL (default) or OFF (performance) | Controls durability vs. performance |
| `cache_size` | 2000-10000 pages | Affects memory usage and read performance |
| `temp_store` | MEMORY | Stores temporary tables in memory |
| `mmap_size` | 0 (disabled) to 1GB+ | Memory-mapped I/O for large databases |

#### 1.3.2 Connection Setup

```php
// Implementation for SQLiteDriver
public function connect(array $config): \PDO
{
    // Create database directory if it doesn't exist
    $dbFile = $config['database'];
    if ($dbFile !== ':memory:') {
        $dbDir = dirname($dbFile);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
    }
    
    // Set options for performance
    $options = $config['options'] ?? [];
    $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
    $options[\PDO::ATTR_DEFAULT_FETCH_MODE] = \PDO::FETCH_ASSOC;
    $options[\PDO::ATTR_EMULATE_PREPARES] = false;
    
    // Create PDO instance
    $pdo = new \PDO(
        $this->buildDsn($config),
        null,
        null,
        $options
    );
    
    // Configure SQLite for performance
    $this->configureSQLite($pdo, $config);
    
    return $pdo;
}

private function configureSQLite(\PDO $pdo, array $config): void
{
    // Enable foreign keys if requested
    if ($config['foreignKeys'] ?? true) {
        $pdo->exec('PRAGMA foreign_keys = ON;');
    }
    
    // Set journal mode (WAL recommended for performance)
    $journalMode = $config['journalMode'] ?? 'WAL';
    $pdo->exec("PRAGMA journal_mode = {$journalMode};");
    
    // Set synchronous mode
    $synchronous = $config['synchronous'] ?? 'NORMAL';
    $pdo->exec("PRAGMA synchronous = {$synchronous};");
    
    // Set cache size (in pages, default page size is 4KB)
    $cacheSize = $config['cacheSize'] ?? 2000;
    $pdo->exec("PRAGMA cache_size = {$cacheSize};");
    
    // Set temp store location
    $tempStore = $config['tempStore'] ?? 'MEMORY';
    $pdo->exec("PRAGMA temp_store = {$tempStore};");
    
    // Set mmap size if specified
    if (isset($config['mmapSize'])) {
        $pdo->exec("PRAGMA mmap_size = {$config['mmapSize']};");
    }
}
```

#### 1.3.3 Transaction Optimization

SQLite performance improves dramatically with proper transaction use:

```php
// Optimized batch operation with transactions
public function batchOperation(callable $operations): void
{
    $this->beginTransaction();
    
    try {
        // Execute the batch operations
        $operations($this);
        
        $this->commit();
    } catch (\Exception $e) {
        $this->rollback();
        throw $e;
    }
}
```

#### 1.3.4 Schema and Index Optimization

- **Denormalization**: Consider denormalizing data for read-heavy operations
- **Careful Indexing**: Indexes improve read performance but slow down writes
- **ANALYZE**: Run ANALYZE periodically to optimize query planning

```php
// Optimize database after schema changes
public function optimize(): void
{
    $this->query('ANALYZE;');
    $this->query('VACUUM;');
}
```

### 1.4 Benchmarking Methodology

To ensure optimal performance across both database systems, implement a benchmarking framework:

#### 1.4.1 Benchmark Metrics

| Metric | Description | Target MySQL | Target SQLite |
|--------|-------------|--------------|---------------|
| **Query Response Time** | Average time to complete queries | <50ms for simple, <200ms for complex | <20ms for simple, <100ms for complex |
| **Transactions Per Second** | Number of transactions processed | 100-1000+ TPS | 50-500 TPS |
| **Connection Overhead** | Time to establish connection | <50ms | <10ms |
| **Memory Usage** | RAM consumed during operation | <100MB per connection | <20MB total |
| **CPU Utilization** | Processor usage during peak load | <50% | <30% |
| **Disk I/O** | Read/write operations per second | Varies by hardware | Varies by hardware |

#### 1.4.2 Benchmark Implementation

```php
// DatabaseBenchmark class (simplified)
class DatabaseBenchmark
{
    private DatabaseORM $db;
    private array $results = [];
    
    public function __construct(DatabaseORM $db)
    {
        $this->db = $db;
    }
    
    public function runBenchmark(string $name, callable $operation, int $iterations = 100): array
    {
        $times = [];
        $memoryUsage = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            $startMemory = memory_get_usage();
            $startTime = microtime(true);
            
            $operation($this->db);
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage();
            
            $times[] = ($endTime - $startTime) * 1000; // Convert to ms
            $memoryUsage[] = $endMemory - $startMemory;
        }
        
        $result = [
            'name' => $name,
            'iterations' => $iterations,
            'avg_time_ms' => array_sum($times) / count($times),
            'min_time_ms' => min($times),
            'max_time_ms' => max($times),
            'avg_memory_bytes' => array_sum($memoryUsage) / count($memoryUsage),
        ];
        
        $this->results[$name] = $result;
        return $result;
    }
    
    public function getResults(): array
    {
        return $this->results;
    }
}
```

#### 1.4.3 Standard Benchmark Suite

Develop a standard suite of benchmarks to run against both database systems:

1. **Simple CRUD Operations**: Single-row operations
2. **Batch Operations**: Multi-row inserts and updates
3. **Complex Queries**: Joins, aggregations, and subqueries
4. **Concurrent Access**: Simulated multi-user access
5. **Transaction Performance**: Nested transactions and rollbacks

### 1.5 Resource Utilization Patterns

Understanding resource utilization patterns helps optimize database configuration:

#### 1.5.1 MySQL Resource Patterns

- **Memory**: Scales with connection count and buffer pool size
- **CPU**: Peaks during complex queries and high concurrency
- **Disk I/O**: Highest during write-heavy operations and when buffer pool is insufficient
- **Network**: Increases with result set size and connection count

**Optimization Strategy**: Configure buffer pool size based on available memory and dataset size. Use connection pooling to reduce connection overhead. Implement query optimization for CPU-intensive operations.

#### 1.5.2 SQLite Resource Patterns

- **Memory**: Primarily affected by cache size and temp store settings
- **CPU**: Peaks during complex queries and when using in-memory databases
- **Disk I/O**: Highest during commit operations in default journal mode
- **Concurrency**: Limited by single-writer design

**Optimization Strategy**: Use WAL mode to improve concurrency. Configure appropriate cache size based on available memory. Use transactions for batch operations to reduce disk I/O.

## 2. Migration Roadmap

### 2.1 Compatibility Assessment

#### 2.1.1 Code Compatibility Analysis

| Component | Compatibility Issues | Mitigation Strategy |
|-----------|----------------------|---------------------|
| **SQL Syntax** | Different dialect features | Abstract through driver-specific methods |
| **Schema Management** | Different DDL syntax | Create schema migration utilities |
| **Transaction Handling** | Different isolation levels | Use common subset of features |
| **Data Types** | Type mapping differences | Create type conversion layer |
| **Concurrency Model** | Different locking mechanisms | Implement appropriate retry logic |

#### 2.1.2 Automated Compatibility Checker

Develop a tool to scan application code for database-specific patterns:

```php
// DatabaseCompatibilityChecker class (conceptual)
class DatabaseCompatibilityChecker
{
    private array $mysqlSpecificPatterns = [
        '/SHOW TABLES/i',
        '/AUTO_INCREMENT/i',
        '/ON DUPLICATE KEY/i',
        // Additional MySQL-specific patterns
    ];
    
    private array $sqliteIncompatiblePatterns = [
        '/STORED PROCEDURE/i',
        '/FOREIGN_KEY_CHECKS/i',
        '/FULLTEXT/i',
        // Additional SQLite-incompatible patterns
    ];
    
    public function checkFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $issues = [];
        
        foreach ($this->mysqlSpecificPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $issues[] = [
                    'file' => $filePath,
                    'pattern' => $pattern,
                    'match' => $matches[0],
                    'type' => 'MySQL-specific'
                ];
            }
        }
        
        foreach ($this->sqliteIncompatiblePatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $issues[] = [
                    'file' => $filePath,
                    'pattern' => $pattern,
                    'match' => $matches[0],
                    'type' => 'SQLite-incompatible'
                ];
            }
        }
        
        return $issues;
    }
    
    public function checkDirectory(string $directory): array
    {
        $issues = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $fileIssues = $this->checkFile($file->getPathname());
                if (!empty($fileIssues)) {
                    $issues = array_merge($issues, $fileIssues);
                }
            }
        }
        
        return $issues;
    }
}
```

### 2.2 Schema Translation Strategy

#### 2.2.1 Schema Mapping

| MySQL Feature | SQLite Equivalent | Translation Approach |
|---------------|-------------------|----------------------|
| `AUTO_INCREMENT` | `AUTOINCREMENT` | Automatic translation in schema creation |
| `ENUM` | `CHECK` constraints | Convert to text with constraints |
| `DATETIME` | `TEXT` (ISO format) | Convert using appropriate functions |
| `JSON` | `TEXT` | Add validation in application layer |
| `FOREIGN KEY` | `FOREIGN KEY` (must be enabled) | Enable foreign keys in SQLite |
| `FULLTEXT INDEX` | Not available | Implement application-level search |

#### 2.2.2 Schema Migration Tool

Create a schema migration tool that can translate between database systems:

```php
// SchemaTranslator class (conceptual)
class SchemaTranslator
{
    private DatabaseORM $sourceDb;
    private DatabaseORM $targetDb;
    
    public function __construct(DatabaseORM $sourceDb, DatabaseORM $targetDb)
    {
        $this->sourceDb = $sourceDb;
        $this->targetDb = $targetDb;
    }
    
    public function migrateTable(string $tableName): void
    {
        // Get source schema
        $sourceSchema = $this->sourceDb->getTableSchema($tableName);
        
        // Translate schema to target format
        $targetSchema = $this->translateSchema($sourceSchema);
        
        // Create table in target database
        $this->createTargetTable($tableName, $targetSchema);
        
        // Migrate data
        $this->migrateData($tableName);
    }
    
    private function translateSchema(array $schema): array
    {
        // Implement schema translation logic
        // Convert data types, constraints, etc.
        
        return $translatedSchema;
    }
    
    private function createTargetTable(string $tableName, array $schema): void
    {
        // Generate CREATE TABLE statement for target database
        $createStatement = $this->generateCreateStatement($tableName, $schema);
        
        // Execute statement on target database
        $this->targetDb->query($createStatement);
    }
    
    private function migrateData(string $tableName): void
    {
        // Fetch data from source in batches
        $offset = 0;
        $limit = 1000;
        
        while (true) {
            $data = $this->sourceDb->read($tableName, [], ['*'], [
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            if (empty($data)) {
                break;
            }
            
            // Transform data if needed
            $transformedData = $this->transformData($data);
            
            // Insert into target database
            foreach ($transformedData as $row) {
                $this->targetDb->create($tableName, $row);
            }
            
            $offset += $limit;
        }
    }
    
    private function transformData(array $data): array
    {
        // Implement data transformation logic
        // Convert data types, formats, etc.
        
        return $transformedData;
    }
}
```

### 2.3 Minimizing Downtime During Migration

#### 2.3.1 Migration Strategies

| Strategy | Description | Downtime | Complexity |
|----------|-------------|----------|------------|
| **Big Bang** | Complete switchover at once | High | Low |
| **Phased Migration** | Migrate components gradually | Medium | Medium |
| **Parallel Operation** | Run both systems simultaneously | Low | High |
| **Shadow Deployment** | Test with production data copy | None | High |

#### 2.3.2 Recommended Approach: Parallel Operation with Synchronization

1. **Setup Phase**:
   - Deploy the new database system
   - Create schema in the new system
   - Implement synchronization mechanism

2. **Initial Data Migration**:
   - Perform bulk data transfer during off-peak hours
   - Validate data integrity

3. **Synchronization Phase**:
   - Implement change data capture (CDC) or dual-write pattern
   - Keep both databases in sync

4. **Testing Phase**:
   - Direct read queries to new database
   - Monitor performance and correctness

5. **Cutover Phase**:
   - Redirect write operations to new database
   - Maintain backward synchronization temporarily

6. **Cleanup Phase**:
   - Remove synchronization mechanism
   - Decommission old database

#### 2.3.3 Synchronization Implementation

```php
// DatabaseSynchronizer class (conceptual)
class DatabaseSynchronizer
{
    private DatabaseORM $sourceDb;
    private DatabaseORM $targetDb;
    private array $tables;
    
    public function __construct(DatabaseORM $sourceDb, DatabaseORM $targetDb, array $tables)
    {
        $this->sourceDb = $sourceDb;
        $this->targetDb = $targetDb;
        $this->tables = $tables;
    }
    
    public function syncChanges(): array
    {
        $stats = [];
        
        foreach ($this->tables as $table) {
            $lastSync = $this->getLastSyncTimestamp($table);
            $changes = $this->getChanges($table, $lastSync);
            
            $stats[$table] = [
                'inserts' => 0,
                'updates' => 0,
                'deletes' => 0
            ];
            
            foreach ($changes as $change) {
                switch ($change['operation']) {
                    case 'INSERT':
                        $this->targetDb->create($table, $change['data']);
                        $stats[$table]['inserts']++;
                        break;
                    case 'UPDATE':
                        $this->targetDb->update($table, $change['data'], ['id' => $change['id']]);
                        $stats[$table]['updates']++;
                        break;
                    case 'DELETE':
                        $this->targetDb->delete($table, ['id' => $change['id']]);
                        $stats[$table]['deletes']++;
                        break;
                }
            }
            
            $this->updateLastSyncTimestamp($table);
        }
        
        return $stats;
    }
    
    private function getChanges(string $table, string $lastSync): array
    {
        // Implementation depends on change tracking mechanism
        // Could use timestamps, version columns, or change logs
        
        return $changes;
    }
    
    private function getLastSyncTimestamp(string $table): string
    {
        // Retrieve last synchronization timestamp
        
        return $timestamp;
    }
    
    private function updateLastSyncTimestamp(string $table): void
    {
        // Update synchronization timestamp
    }
}
```

### 2.4 Data Validation Methodologies

#### 2.4.1 Validation Levels

| Level | Description | When to Use |
|-------|-------------|-------------|
| **Schema Validation** | Verify table structures match | After schema migration |
| **Count Validation** | Compare record counts | Quick integrity check |
| **Checksum Validation** | Compare data checksums | Efficient full comparison |
| **Full Data Comparison** | Compare all data values | Thorough validation |
| **Application-Level Validation** | Test through application | Final verification |

#### 2.4.2 Validation Implementation

```php
// DataValidator class (conceptual)
class DataValidator
{
    private DatabaseORM $sourceDb;
    private DatabaseORM $targetDb;
    
    public function __construct(DatabaseORM $sourceDb, DatabaseORM $targetDb)
    {
        $this->sourceDb = $sourceDb;
        $this->targetDb = $targetDb;
    }
    
    public function validateSchema(string $table): array
    {
        $sourceSchema = $this->sourceDb->getTableSchema($table);
        $targetSchema = $this->targetDb->getTableSchema($table);
        
        $differences = [];
        
        // Compare columns
        foreach ($sourceSchema as $column) {
            $found = false;
            
            foreach ($targetSchema as $targetColumn) {
                if ($column['Field'] === $targetColumn['Field']) {
                    $found = true;
                    
                    // Compare column properties
                    if ($this->normalizeType($column['Type']) !== $this->normalizeType($targetColumn['Type'])) {
                        $differences[] = [
                            'column' => $column['Field'],
                            'issue' => 'type_mismatch',
                            'source' => $column['Type'],
                            'target' => $targetColumn['Type']
                        ];
                    }
                    
                    break;
                }
            }
            
            if (!$found) {
                $differences[] = [
                    'column' => $column['Field'],
                    'issue' => 'missing_in_target'
                ];
            }
        }
        
        return $differences;
    }
    
    public function validateCounts(string $table): array
    {
        $sourceCount = $this->sourceDb->query("SELECT COUNT(*) as count FROM {$table}")[0]['count'];
        $targetCount = $this->targetDb->query("SELECT COUNT(*) as count FROM {$table}")[0]['count'];
        
        return [
            'table' => $table,
            'source_count' => $sourceCount,
            'target_count' => $targetCount,
            'match' => $sourceCount === $targetCount
        ];
    }
    
    public function validateData(string $table, string $primaryKey = 'id'): array
    {
        $results = [
            'table' => $table,
            'records_checked' => 0,
            'mismatches' => []
        ];
        
        $sourceData = $this->sourceDb->read($table);
        
        foreach ($sourceData as $row) {
            $results['records_checked']++;
            
            $targetRow = $this->targetDb->read($table, [$primaryKey => $row[$primaryKey]]);
            
            if (empty($targetRow)) {
                $results['mismatches'][] = [
                    $primaryKey => $row[$primaryKey],
                    'issue' => 'missing_in_target'
                ];
                continue;
            }
            
            $targetRow = $targetRow[0];
            
            foreach ($row as $column => $value) {
                if ($this->normalizeValue($value) !== $this->normalizeValue($targetRow[$column] ?? null)) {
                    $results['mismatches'][] = [
                        $primaryKey => $row[$primaryKey],
                        'column' => $column,
                        'source_value' => $value,
                        'target_value' => $targetRow[$column] ?? null
                    ];
                }
            }
        }
        
        return $results;
    }
    
    private function normalizeType(string $type): string
    {
        // Normalize type strings for comparison
        // e.g., INT(11) -> INTEGER, VARCHAR(255) -> TEXT
        
        return $normalizedType;
    }
    
    private function normalizeValue($value)
    {
        // Normalize values for comparison
        // Handle type conversions, date formats, etc.
        
        return $normalizedValue;
    }
}
```

### 2.5 Application Code Refactoring

#### 2.5.1 Refactoring Approach

| Component | Refactoring Strategy | Priority |
|-----------|----------------------|----------|
| **Raw SQL Queries** | Replace with ORM methods | High |
| **MySQL Functions** | Replace with database-agnostic alternatives | High |
| **Transaction Code** | Standardize transaction handling | Medium |
| **Schema Definitions** | Move to migration files | Medium |
| **Connection Management** | Use factory pattern | Low |

#### 2.5.2 Code Refactoring Examples

**Before (MySQL-specific):**
```php
// Direct MySQL query with specific functions
$result = $db->query("
    SELECT *, DATE_FORMAT(created_at, '%Y-%m-%d') as formatted_date 
    FROM users 
    WHERE YEAR(created_at) = 2024
    ORDER BY created_at DESC
");
```

**After (Database-agnostic):**
```php
// Using ORM methods with driver-specific handling
$users = $db->read(
    'users',
    [],
    ['*'],
    [
        'orderBy' => 'created_at',
        'orderDirection' => 'DESC'
    ]
);

// Format dates in PHP (database-agnostic)
foreach ($users as &$user) {
    $date = new \DateTime($user['created_at']);
    $user['formatted_date'] = $date->format('Y-m-d');
    
    // Filter by year in PHP
    if ($date->format('Y') != '2024') {
        unset($user);
    }
}
```

#### 2.5.3 Refactoring Tools

Develop tools to assist with code refactoring:

```php
// SQLRefactorer class (conceptual)
class SQLRefactorer
{
    private array $replacementPatterns = [
        // MySQL date functions
        '/DATE_FORMAT\(([^,]+),\s*\'([^\']+)\'\)/i' => 'formatDate($1, "$2")',
        
        // MySQL string functions
        '/CONCAT\(([^)]+)\)/i' => 'concatenate($1)',
        
        // MySQL-specific operators
        '/([^ ]+)\s+REGEXP\s+([^ ]+)/i' => 'regexMatch($1, $2)',
        
        // Additional patterns
    ];
    
    public function refactorQuery(string $sql): string
    {
        $refactored = $sql;
        
        foreach ($this->replacementPatterns as $pattern => $replacement) {
            $refactored = preg_replace($pattern, $replacement, $refactored);
        }
        
        return $refactored;
    }
    
    public function analyzeFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $matches = [];
        
        // Find SQL queries in the file
        preg_match_all('/\$db->query\(\s*"([^"]+)"\s*\)/i', $content, $matches);
        
        $results = [];
        
        foreach ($matches[1] as $query) {
            $refactored = $this->refactorQuery($query);
            
            if ($refactored !== $query) {
                $results[] = [
                    'original' => $query,
                    'refactored' => $refactored
                ];
            }
        }
        
        return $results;
    }
}
```

### 2.6 Maintaining Backward Compatibility

#### 2.6.1 Compatibility Layer

Implement a compatibility layer to support both database systems during transition:

```php
// DatabaseCompatibilityLayer class (conceptual)
class DatabaseCompatibilityLayer
{
    private DatabaseORM $primaryDb;
    private ?DatabaseORM $secondaryDb = null;
    private bool $dualWriteEnabled = false;
    
    public function __construct(DatabaseORM $primaryDb)
    {
        $this->primaryDb = $primaryDb;
    }
    
    public function setSecondaryDatabase(DatabaseORM $db): void
    {
        $this->secondaryDb = $db;
    }
    
    public function enableDualWrite(bool $enabled = true): void
    {
        $this->dualWriteEnabled = $enabled && $this->secondaryDb !== null;
    }
    
    public function create(string $table, array $data): int|string
    {
        $id = $this->primaryDb->create($table, $data);
        
        if ($this->dualWriteEnabled) {
            try {
                // Use the