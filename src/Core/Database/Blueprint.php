<?php

namespace LogbieCore\Database;

/**
 * Blueprint
 * 
 * Represents a table blueprint for schema building operations.
 * Provides a fluent interface for defining table structure.
 * 
 * @package LogbieCore\Database
 * @since 1.0.0
 */
class Blueprint
{
    /**
     * The table name
     * 
     * @var string
     */
    private string $table;
    
    /**
     * The operation type (create, alter, drop)
     * 
     * @var string
     */
    private string $operation;
    
    /**
     * The columns to be created or modified
     * 
     * @var array
     */
    private array $columns = [];
    
    /**
     * The indexes to be created
     * 
     * @var array
     */
    private array $indexes = [];
    
    /**
     * The columns to be dropped
     * 
     * @var array
     */
    private array $dropColumns = [];
    
    /**
     * Constructor
     * 
     * @param string $table The table name
     * @param string $operation The operation type
     */
    public function __construct(string $table, string $operation = 'create')
    {
        $this->table = $table;
        $this->operation = $operation;
    }
    
    /**
     * Get the table name
     * 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * Get the operation type
     * 
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }
    
    /**
     * Get all columns
     * 
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }
    
    /**
     * Get all indexes
     * 
     * @return array
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }
    
    /**
     * Get columns to be dropped
     * 
     * @return array
     */
    public function getDropColumns(): array
    {
        return $this->dropColumns;
    }
    
    /**
     * Add an auto-incrementing ID column
     * 
     * @param string $name The column name (default: 'id')
     * @return self
     */
    public function id(string $name = 'id'): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'id',
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => true,
            'autoIncrement' => true
        ];
        
        return $this;
    }
    
    /**
     * Add a string column
     * 
     * @param string $name The column name
     * @param int $length The column length (default: 255)
     * @return ColumnDefinition
     */
    public function string(string $name, int $length = 255): ColumnDefinition
    {
        $column = [
            'name' => $name,
            'type' => 'string',
            'length' => $length,
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'autoIncrement' => false
        ];
        
        $this->columns[] = $column;
        
        return new ColumnDefinition($this, count($this->columns) - 1);
    }
    
    /**
     * Add an integer column
     * 
     * @param string $name The column name
     * @return ColumnDefinition
     */
    public function integer(string $name): ColumnDefinition
    {
        $column = [
            'name' => $name,
            'type' => 'integer',
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'autoIncrement' => false
        ];
        
        $this->columns[] = $column;
        
        return new ColumnDefinition($this, count($this->columns) - 1);
    }
    
    /**
     * Add a boolean column
     * 
     * @param string $name The column name
     * @return ColumnDefinition
     */
    public function boolean(string $name): ColumnDefinition
    {
        $column = [
            'name' => $name,
            'type' => 'boolean',
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'autoIncrement' => false
        ];
        
        $this->columns[] = $column;
        
        return new ColumnDefinition($this, count($this->columns) - 1);
    }
    
    /**
     * Add a text column
     * 
     * @param string $name The column name
     * @return ColumnDefinition
     */
    public function text(string $name): ColumnDefinition
    {
        $column = [
            'name' => $name,
            'type' => 'text',
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'autoIncrement' => false
        ];
        
        $this->columns[] = $column;
        
        return new ColumnDefinition($this, count($this->columns) - 1);
    }
    
    /**
     * Add a timestamp column
     * 
     * @param string $name The column name
     * @return ColumnDefinition
     */
    public function timestamp(string $name): ColumnDefinition
    {
        $column = [
            'name' => $name,
            'type' => 'timestamp',
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'autoIncrement' => false
        ];
        
        $this->columns[] = $column;
        
        return new ColumnDefinition($this, count($this->columns) - 1);
    }
    
    /**
     * Add created_at and updated_at timestamp columns
     * 
     * @return self
     */
    public function timestamps(): self
    {
        $this->timestamp('created_at')->default('CURRENT_TIMESTAMP');
        $this->timestamp('updated_at')->nullable()->default(null);
        
        return $this;
    }
    
    /**
     * Add a column to be dropped (for alter operations)
     * 
     * @param string $name The column name
     * @return self
     */
    public function dropColumn(string $name): self
    {
        $this->dropColumns[] = $name;
        
        return $this;
    }
    
    /**
     * Add a column (for alter operations)
     * 
     * @param string $name The column name
     * @param string $type The column type
     * @return ColumnDefinition
     */
    public function addColumn(string $name, string $type): ColumnDefinition
    {
        $column = [
            'name' => $name,
            'type' => $type,
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'autoIncrement' => false,
            'action' => 'add'
        ];
        
        $this->columns[] = $column;
        
        return new ColumnDefinition($this, count($this->columns) - 1);
    }
    
    /**
     * Update a column definition at a specific index
     * 
     * @param int $index The column index
     * @param array $attributes The attributes to update
     * @return void
     */
    public function updateColumn(int $index, array $attributes): void
    {
        if (isset($this->columns[$index])) {
            $this->columns[$index] = array_merge($this->columns[$index], $attributes);
        }
    }
}

/**
 * Column Definition
 * 
 * Provides a fluent interface for defining column properties.
 */
class ColumnDefinition
{
    /**
     * The blueprint instance
     * 
     * @var Blueprint
     */
    private Blueprint $blueprint;
    
    /**
     * The column index in the blueprint
     * 
     * @var int
     */
    private int $columnIndex;
    
    /**
     * Constructor
     * 
     * @param Blueprint $blueprint The blueprint instance
     * @param int $columnIndex The column index
     */
    public function __construct(Blueprint $blueprint, int $columnIndex)
    {
        $this->blueprint = $blueprint;
        $this->columnIndex = $columnIndex;
    }
    
    /**
     * Make the column nullable
     * 
     * @return self
     */
    public function nullable(): self
    {
        $this->blueprint->updateColumn($this->columnIndex, ['nullable' => true]);
        
        return $this;
    }
    
    /**
     * Set a default value for the column
     * 
     * @param mixed $value The default value
     * @return self
     */
    public function default($value): self
    {
        $this->blueprint->updateColumn($this->columnIndex, ['default' => $value]);
        
        return $this;
    }
    
    /**
     * Make the column unique
     * 
     * @return self
     */
    public function unique(): self
    {
        $this->blueprint->updateColumn($this->columnIndex, ['unique' => true]);
        
        return $this;
    }
    
    /**
     * Make the column a primary key
     * 
     * @return self
     */
    public function primary(): self
    {
        $this->blueprint->updateColumn($this->columnIndex, ['primary' => true]);
        
        return $this;
    }
}
