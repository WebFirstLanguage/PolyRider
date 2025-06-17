<?php

namespace LogbieCore\Database;

use LogbieCore\DatabaseORM;

/**
 * Migration
 * 
 * Abstract base class for all database migrations.
 * Provides access to the Schema Builder and Database ORM.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
abstract class Migration
{
    /**
     * The schema builder instance
     * 
     * @var SchemaBuilder
     */
    protected SchemaBuilder $schema;
    
    /**
     * The database ORM instance
     * 
     * @var DatabaseORM
     */
    protected DatabaseORM $db;
    
    /**
     * Constructor
     * 
     * @param DatabaseORM $db The database ORM instance
     */
    public function __construct(DatabaseORM $db)
    {
        $this->db = $db;
        $this->schema = new SchemaBuilder($db);
    }
    
    /**
     * Run the migration (apply schema changes)
     * 
     * @return void
     */
    abstract public function up(): void;
    
    /**
     * Reverse the migration (revert schema changes)
     * 
     * @return void
     */
    abstract public function down(): void;
    
    /**
     * Get the database ORM instance
     * 
     * @return DatabaseORM
     */
    protected function getDatabase(): DatabaseORM
    {
        return $this->db;
    }
    
    /**
     * Get the schema builder instance
     * 
     * @return SchemaBuilder
     */
    protected function getSchema(): SchemaBuilder
    {
        return $this->schema;
    }
}
