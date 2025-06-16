# Logbie Framework: Database Integration Strategy - Addendum (Part 2)

## 2.6 Maintaining Backward Compatibility (continued)

#### 2.6.1 Compatibility Layer (continued)

```php
// DatabaseCompatibilityLayer class (continued)
public function create(string $table, array $data): int|string
{
    $id = $this->primaryDb->create($table, $data);
    
    if ($this->dualWriteEnabled) {
        try {
            // Use the same ID for the secondary database
            $secondaryData = $data;
            $primaryKey = $this->getPrimaryKey($table);
            if ($primaryKey && !isset($secondaryData[$primaryKey])) {
                $secondaryData[$primaryKey] = $id;
            }
            
            $this->secondaryDb->create($table, $secondaryData);
        } catch (\Exception $e) {
            // Log the error but don't fail the operation
            $this->logSyncError('create', $table, $data, $e);
        }
    }
    
    return $id;
}

public function read(
    string $table,
    array $conditions = [],
    array $columns = ['*'],
    array $options = []
): array {
    // Always read from primary database
    return $this->primaryDb->read($table, $conditions, $columns, $options);
}

public function update(string $table, array $data, array $conditions): int
{
    $affected = $this->primaryDb->update($table, $data, $conditions);
    
    if ($this->dualWriteEnabled && $affected > 0) {
        try {
            $this->secondaryDb->update($table, $data, $conditions);
        } catch (\Exception $e) {
            $this->logSyncError('update', $table, $data, $e);
        }
    }
    
    return $affected;
}

public function delete(string $table, array $conditions): int
{
    $affected = $this->primaryDb->delete($table, $conditions);
    
    if ($this->dualWriteEnabled && $affected > 0) {
        try {
            $this->secondaryDb->delete($table, $conditions);
        } catch (\Exception $e) {
            $this->logSyncError('delete', $table, $conditions, $e);
        }
    }
    
    return $affected;
}

private function getPrimaryKey(string $table): ?string
{
    // Get primary key for the table
    $schema = $this->primaryDb->getTableSchema($table);
    
    foreach ($schema as $column) {
        if ($column['Key'] === 'PRI') {
            return $column['Field'];
        }
    }
    
    return null;
}

private function logSyncError(string $operation, string $table, array $data, \Exception $exception): void
{
    // Log synchronization error
    // This would typically use the framework's logger
    error_log(sprintf(
        'Database sync error: %s on %s failed: %s',
        $operation,
        $table,
        $exception->getMessage()
    ));
}
```

#### 2.6.2 Feature Flags

Implement feature flags to control database behavior:

```php
// DatabaseFeatureFlags class (conceptual)
class DatabaseFeatureFlags
{
    private array $flags = [
        'use_sqlite' => false,
        'dual_write' => false,
        'read_from_sqlite' => false,
        'use_compatibility_layer' => true
    ];
    
    public function __construct(array $initialFlags = [])
    {
        $this->flags = array_merge($this->flags, $initialFlags);
    }
    
    public function isEnabled(string $flag): bool
    {
        return $this->flags[$flag] ?? false;
    }
    
    public function enable(string $flag): void
    {
        $this->flags[$flag] = true;
    }
    
    public function disable(string $flag): void
    {
        $this->flags[$flag] = false;
    }
}
```

#### 2.6.3 Database Factory with Feature Flags

```php
// DatabaseFactory class (conceptual)
class DatabaseFactory
{
    private Container $container;
    private DatabaseFeatureFlags $featureFlags;
    
    public function __construct(Container $container, DatabaseFeatureFlags $featureFlags)
    {
        $this->container = $container;
        $this->featureFlags = $featureFlags;
    }
    
    public function createDatabase(): DatabaseORM
    {
        if ($this->featureFlags->isEnabled('use_compatibility_layer')) {
            return $this->createCompatibilityLayer();
        }
        
        if ($this->featureFlags->isEnabled('use_sqlite')) {
            return $this->createSQLiteDatabase();
        }
        
        return $this->createMySQLDatabase();
    }
    
    private function createCompatibilityLayer(): DatabaseCompatibilityLayer
    {
        $primaryDb = $this->featureFlags->isEnabled('read_from_sqlite')
            ? $this->createSQLiteDatabase()
            : $this->createMySQLDatabase();
        
        $layer = new DatabaseCompatibilityLayer($primaryDb);
        
        if ($this->featureFlags->isEnabled('dual_write')) {
            $secondaryDb = $this->featureFlags->isEnabled('read_from_sqlite')
                ? $this->createMySQLDatabase()
                : $this->createSQLiteDatabase();
            
            $layer->setSecondaryDatabase($secondaryDb);
            $layer->enableDualWrite();
        }
        
        return $layer;
    }
    
    private function createMySQLDatabase(): DatabaseORM
    {
        $config = $this->container->get('config.database.mysql');
        $driver = new MySQLDriver();
        return new DatabaseORM($driver, $config);
    }
    
    private function createSQLiteDatabase(): DatabaseORM
    {
        $config = $this->container->get('config.database.sqlite');
        $driver = new SQLiteDriver();
        return new DatabaseORM($driver, $config);
    }
}
```

## 3. Implementation Timeline and Milestones

### 3.1 Phase 1: Foundation (Weeks 1-2)

| Milestone | Description | Deliverables |
|-----------|-------------|--------------|
| **Architecture Design** | Finalize driver-based architecture | Detailed design document |
| **Interface Definition** | Define DatabaseDriver interface | Interface code with documentation |
| **MySQL Driver** | Extract MySQL-specific code | MySQLDriver implementation |
| **Unit Tests** | Create test suite for drivers | Test cases for MySQL driver |

### 3.2 Phase 2: SQLite Implementation (Weeks 3-4)

| Milestone | Description | Deliverables |
|-----------|-------------|--------------|
| **SQLite Driver** | Implement SQLite driver | SQLiteDriver implementation |
| **Configuration** | Enhance configuration system | Updated configuration structure |
| **Unit Tests** | Test SQLite implementation | Test cases for SQLite driver |
| **Performance Benchmarks** | Baseline performance tests | Benchmark results for both drivers |

### 3.3 Phase 3: Migration Tools (Weeks 5-6)

| Milestone | Description | Deliverables |
|-----------|-------------|--------------|
| **Schema Translator** | Implement schema migration | SchemaTranslator implementation |
| **Data Validator** | Create data validation tools | DataValidator implementation |
| **Compatibility Checker** | Develop code analysis tool | DatabaseCompatibilityChecker implementation |
| **Documentation** | Create migration guide | Migration documentation |

### 3.4 Phase 4: Compatibility Layer (Weeks 7-8)

| Milestone | Description | Deliverables |
|-----------|-------------|--------------|
| **Compatibility Layer** | Implement dual-write support | DatabaseCompatibilityLayer implementation |
| **Feature Flags** | Add feature flag system | DatabaseFeatureFlags implementation |
| **Factory Pattern** | Create database factory | DatabaseFactory implementation |
| **Integration Tests** | Test compatibility layer | Integration test suite |

### 3.5 Phase 5: Optimization and Finalization (Weeks 9-10)

| Milestone | Description | Deliverables |
|-----------|-------------|--------------|
| **Performance Tuning** | Optimize both drivers | Optimized driver implementations |
| **Advanced Features** | Add additional features | Enhanced functionality |
| **Documentation** | Complete all documentation | Comprehensive documentation |
| **Example Applications** | Create example applications | Sample code for both databases |

## 4. Conclusion

This addendum provides detailed performance considerations and a comprehensive migration roadmap for implementing SQLite support in the Logbie Framework. By following this plan, the framework can maintain high performance while supporting both MySQL and SQLite databases.

The key to successful implementation lies in:

1. **Proper Abstraction**: Using the driver pattern to abstract database-specific details
2. **Performance Optimization**: Configuring each database system for optimal performance
3. **Careful Migration**: Using a phased approach to minimize disruption
4. **Thorough Testing**: Validating functionality and performance across both systems
5. **Backward Compatibility**: Maintaining support for existing applications during transition

With this approach, the Logbie Framework will gain the flexibility to work with both database systems while maintaining its core principles of security, performance, and maintainability.