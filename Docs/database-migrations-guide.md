# Database Migrations Guide

The Logbie Framework includes a powerful, database-agnostic migration system that allows you to version control your database schema changes. This guide covers how to use the migration system effectively.

## Overview

The migration system provides four main commands:
- `migrate:make` - Create new migration files
- `migrate` - Execute pending migrations
- `migrate:rollback` - Rollback the last batch of migrations
- `migrate:status` - View migration status

All migrations are stored in the `database/migrations/` directory and are executed in chronological order based on their timestamps.

## Migration Commands

### Creating Migrations

Use the `migrate:make` command to create a new migration file:

```bash
./logbie migrate:make CreateUsersTable
./logbie migrate:make AddEmailToUsersTable
./logbie migrate:make DropOldTable
```

This creates a timestamped file in `database/migrations/` with the format:
```
YYYY_MM_DD_His_migration_name.php
```

Example: `2024_01_15_143022_create_users_table.php`

### Running Migrations

Execute all pending migrations:

```bash
./logbie migrate
```

This command:
1. Checks for pending migrations in `database/migrations/`
2. Executes each migration's `up()` method in chronological order
3. Records each migration in the `migrations` table
4. Groups all migrations from this run in the same batch number

### Rolling Back Migrations

Rollback the last batch of migrations:

```bash
./logbie migrate:rollback
```

This command:
1. Finds the latest batch of migrations
2. Executes each migration's `down()` method in reverse order
3. Removes the migration records from the `migrations` table

### Checking Migration Status

View the status of all migrations:

```bash
./logbie migrate:status
```

This shows a table with all migration files and their status (executed or pending).

## Schema Builder API

The migration system uses a database-agnostic Schema Builder that works with both MySQL and SQLite. Here are the available methods:

### Creating Tables

```php
public function up(): void
{
    $this->schema->create('users', function($table) {
        $table->id();                           // Auto-incrementing primary key
        $table->string('username')->unique();   // Unique string column
        $table->string('email');                // String column
        $table->string('password');             // String column
        $table->boolean('active')->default(true); // Boolean with default
        $table->integer('age')->nullable();     // Nullable integer
        $table->text('bio');                    // Text column
        $table->timestamps();                   // created_at and updated_at
    });
}
```

### Modifying Tables

```php
public function up(): void
{
    $this->schema->table('users', function($table) {
        $table->addColumn('phone', 'string')->nullable();
        $table->addColumn('verified', 'boolean')->default(false);
    });
}
```

### Dropping Tables

```php
public function down(): void
{
    $this->schema->drop('users');
    // or
    $this->schema->dropIfExists('users');
}
```

### Available Column Types

- `id()` - Auto-incrementing primary key
- `string(name, length = 255)` - VARCHAR column
- `text(name)` - TEXT column
- `integer(name)` - INTEGER column
- `boolean(name)` - BOOLEAN column
- `timestamps()` - Adds created_at and updated_at columns

### Column Modifiers

- `nullable()` - Allow NULL values
- `default(value)` - Set default value
- `unique()` - Add unique constraint

## Migration File Structure

Each migration file contains a class that extends the `Migration` base class:

```php
<?php

use LogbieCore\Database\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('users', function($table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->schema->dropIfExists('users');
    }
}
```

## Migration Tracking

The system automatically creates a `migrations` table to track executed migrations:

```sql
CREATE TABLE migrations (
    migration VARCHAR(255) PRIMARY KEY,
    batch INTEGER NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

- `migration` - The filename of the migration
- `batch` - Groups migrations that were run together
- `executed_at` - When the migration was executed

## Best Practices

### 1. Keep Migrations Small and Focused

Each migration should handle a single, specific change:

```php
// Good: Single purpose
class AddEmailToUsersTable extends Migration { ... }

// Avoid: Multiple unrelated changes
class UpdateUsersAndCreatePostsAndModifyRoles extends Migration { ... }
```

### 2. Always Implement Both up() and down()

Every migration should be reversible:

```php
public function up(): void
{
    $this->schema->create('posts', function($table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->timestamps();
    });
}

public function down(): void
{
    $this->schema->dropIfExists('posts');
}
```

### 3. Use Descriptive Migration Names

Migration names should clearly describe what they do:

```bash
# Good
./logbie migrate:make CreateUsersTable
./logbie migrate:make AddIndexToUsersEmail
./logbie migrate:make RemoveDeprecatedColumns

# Avoid
./logbie migrate:make UpdateStuff
./logbie migrate:make FixDatabase
```

### 4. Test Migrations Thoroughly

Always test both the `up()` and `down()` methods:

```bash
# Run the migration
./logbie migrate

# Verify it worked
./logbie migrate:status

# Test rollback
./logbie migrate:rollback

# Verify rollback worked
./logbie migrate:status
```

### 5. Backup Before Production Migrations

Always backup your production database before running migrations, especially when dropping tables or columns.

## Troubleshooting

### Migration Fails to Execute

If a migration fails:

1. Check the error message in the console output
2. Verify your Schema Builder syntax
3. Ensure the database connection is working
4. Check that required tables/columns exist

### Migration Table Not Found

If you get "migrations table not found" errors:

```bash
# The system should auto-create the table, but you can force it by running:
./logbie migrate
```

### Rollback Fails

If rollback fails:

1. Check that your `down()` method is properly implemented
2. Verify that the tables/columns you're trying to drop exist
3. Check for foreign key constraints that might prevent dropping

### Schema Builder Errors

Common Schema Builder issues:

```php
// Wrong: Raw SQL
$this->db->query('CREATE TABLE users ...');

// Correct: Use Schema Builder
$this->schema->create('users', function($table) {
    // ...
});
```

### File Permission Issues

If you can't create migration files:

```bash
# Ensure the migrations directory is writable
chmod 755 database/migrations/
```

## Advanced Usage

### Conditional Migrations

You can add conditional logic to migrations:

```php
public function up(): void
{
    if (!$this->schema->hasTable('users')) {
        $this->schema->create('users', function($table) {
            // ...
        });
    }
}
```

### Data Migrations

You can also migrate data within migrations:

```php
public function up(): void
{
    // First, create the table
    $this->schema->create('settings', function($table) {
        $table->id();
        $table->string('key')->unique();
        $table->text('value');
    });
    
    // Then, insert default data
    $this->db->create('settings', [
        'key' => 'site_name',
        'value' => 'My Website'
    ]);
}
```

### Multiple Database Support

The migration system works with both MySQL and SQLite automatically. The Schema Builder handles the differences between database engines transparently.

## Examples

### Complete User System Migration

```php
<?php

use LogbieCore\Database\Migration;

class CreateUserSystemTables extends Migration
{
    public function up(): void
    {
        // Create users table
        $this->schema->create('users', function($table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
        
        // Create roles table
        $this->schema->create('roles', function($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        
        // Create user_roles pivot table
        $this->schema->create('user_roles', function($table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('role_id');
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        $this->schema->dropIfExists('user_roles');
        $this->schema->dropIfExists('roles');
        $this->schema->dropIfExists('users');
    }
}
```

This migration system provides a robust, database-agnostic way to manage your schema changes while maintaining version control and rollback capabilities.
