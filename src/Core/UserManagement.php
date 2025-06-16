<?php

namespace LogbieCore;

/**
 * UserManagement Class
 * 
 * A core service for secure user creation, authentication, and account management.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class UserManagement
{
    /**
     * Password hashing algorithm
     * 
     * @var string
     */
    private const PASSWORD_ALGO = PASSWORD_BCRYPT;
    
    /**
     * Password hashing cost
     * 
     * @var int
     */
    private const PASSWORD_COST = 12;
    
    /**
     * Minimum username length
     * 
     * @var int
     */
    private const MIN_USERNAME_LENGTH = 3;
    
    /**
     * Minimum password length
     * 
     * @var int
     */
    private const MIN_PASSWORD_LENGTH = 8;
    
    /**
     * Database ORM instance
     * 
     * @var DatabaseORM
     */
    private DatabaseORM $db;
    
    /**
     * Logger instance
     * 
     * @var Logger
     */
    private Logger $logger;
    
    /**
     * Constructor
     * 
     * @param DatabaseORM $db The database ORM instance
     * @param Logger $logger The logger instance
     */
    public function __construct(DatabaseORM $db, Logger $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }
    
    /**
     * Create a new user
     * 
     * @param string $username The username
     * @param string $email The email address
     * @param string $password The password
     * @return int The user ID
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If user creation fails
     */
    public function createUser(string $username, string $email, string $password): int
    {
        // Validate input
        $this->validateUsername($username);
        $this->validateEmail($email);
        $this->validatePassword($password);
        
        // Check if username or email already exists
        $existingUser = $this->db->read('users', [
            'username' => $username
        ]);
        
        if (!empty($existingUser)) {
            throw new \RuntimeException('Username already taken');
        }
        
        $existingEmail = $this->db->read('users', [
            'email' => $email
        ]);
        
        if (!empty($existingEmail)) {
            throw new \RuntimeException('Email already registered');
        }
        
        // Hash the password
        $passwordHash = $this->hashPassword($password);
        
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Create the user
            $userId = $this->db->create('users', [
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'active' => true,
                'email_verified' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Commit transaction
            $this->db->commit();
            
            // Log the user creation
            $this->logger->log("User created: {$username} (ID: {$userId})");
            
            return $userId;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            
            // Log the error
            $this->logger->log("User creation failed: {$e->getMessage()}");
            
            throw new \RuntimeException('User creation failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Authenticate a user
     * 
     * @param string $username The username
     * @param string $password The password
     * @return array|null User data on success, null on failure, or array with error on inactive account
     */
    public function authenticateUser(string $username, string $password): ?array
    {
        // Get the user by username
        $users = $this->db->read('users', [
            'username' => $username
        ]);
        
        if (empty($users)) {
            // Log failed login attempt
            $this->logger->log("Authentication failed: Username not found - {$username}");
            return null;
        }
        
        $user = $users[0];
        
        // Verify the password
        if (!password_verify($password, $user['password_hash'])) {
            // Log failed login attempt
            $this->logger->log("Authentication failed: Invalid password for {$username}");
            return null;
        }
        
        // Check if the account is active
        if (!$user['active']) {
            // Log inactive account attempt
            $this->logger->log("Authentication failed: Inactive account - {$username}");
            return ['error' => 'Account is inactive'];
        }
        
        // Update last login time
        $this->db->update('users', [
            'last_login' => date('Y-m-d H:i:s')
        ], [
            'id' => $user['id']
        ]);
        
        // Log successful login
        $this->logger->log("User authenticated: {$username} (ID: {$user['id']})");
        
        // Remove password hash from the returned data
        unset($user['password_hash']);
        
        return $user;
    }
    
    /**
     * Get a user by ID
     * 
     * @param int $userId The user ID
     * @return array|null The user data or null if not found
     */
    public function getUserById(int $userId): ?array
    {
        $users = $this->db->read('users', [
            'id' => $userId
        ]);
        
        if (empty($users)) {
            return null;
        }
        
        $user = $users[0];
        
        // Remove password hash from the returned data
        unset($user['password_hash']);
        
        return $user;
    }
    
    /**
     * Deactivate a user
     * 
     * @param int $userId The user ID
     * @param string $reason The reason for deactivation
     * @return bool True on success
     * @throws \RuntimeException If deactivation fails
     */
    public function deactivateUser(int $userId, string $reason): bool
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Deactivate the user
            $affected = $this->db->update('users', [
                'active' => false,
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'id' => $userId
            ]);
            
            if ($affected === 0) {
                throw new \RuntimeException('User not found');
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Log the deactivation
            $this->logger->log("User deactivated: ID {$userId}, Reason: {$reason}");
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            
            // Log the error
            $this->logger->log("User deactivation failed: {$e->getMessage()}");
            
            throw new \RuntimeException('User deactivation failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Update a user's email
     * 
     * @param int $userId The user ID
     * @param string $email The new email address
     * @return bool True on success
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If update fails
     */
    public function updateEmail(int $userId, string $email): bool
    {
        // Validate email
        $this->validateEmail($email);
        
        // Check if email already exists
        $existingEmail = $this->db->read('users', [
            'email' => $email
        ]);
        
        if (!empty($existingEmail)) {
            throw new \RuntimeException('Email already registered');
        }
        
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update the email
            $affected = $this->db->update('users', [
                'email' => $email,
                'email_verified' => false,
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'id' => $userId
            ]);
            
            if ($affected === 0) {
                throw new \RuntimeException('User not found');
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Log the email update
            $this->logger->log("User email updated: ID {$userId}, New email: {$email}");
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            
            // Log the error
            $this->logger->log("Email update failed: {$e->getMessage()}");
            
            throw new \RuntimeException('Email update failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Update a user's password
     * 
     * @param int $userId The user ID
     * @param string $password The new password
     * @return bool True on success
     * @throws \InvalidArgumentException If validation fails
     * @throws \RuntimeException If update fails
     */
    public function updatePassword(int $userId, string $password): bool
    {
        // Validate password
        $this->validatePassword($password);
        
        // Hash the password
        $passwordHash = $this->hashPassword($password);
        
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update the password
            $affected = $this->db->update('users', [
                'password_hash' => $passwordHash,
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'id' => $userId
            ]);
            
            if ($affected === 0) {
                throw new \RuntimeException('User not found');
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Log the password update
            $this->logger->log("User password updated: ID {$userId}");
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            
            // Log the error
            $this->logger->log("Password update failed: {$e->getMessage()}");
            
            throw new \RuntimeException('Password update failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Verify a user's email
     * 
     * @param int $userId The user ID
     * @return bool True on success
     * @throws \RuntimeException If verification fails
     */
    public function verifyEmail(int $userId): bool
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Verify the email
            $affected = $this->db->update('users', [
                'email_verified' => true,
                'updated_at' => date('Y-m-d H:i:s')
            ], [
                'id' => $userId
            ]);
            
            if ($affected === 0) {
                throw new \RuntimeException('User not found');
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Log the email verification
            $this->logger->log("User email verified: ID {$userId}");
            
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollback();
            
            // Log the error
            $this->logger->log("Email verification failed: {$e->getMessage()}");
            
            throw new \RuntimeException('Email verification failed: ' . $e->getMessage(), 0, $e);
        }
    }
    
    /**
     * Validate a username
     * 
     * @param string $username The username to validate
     * @return bool True if valid
     * @throws \InvalidArgumentException If validation fails
     */
    private function validateUsername(string $username): bool
    {
        if (strlen($username) < self::MIN_USERNAME_LENGTH) {
            throw new \InvalidArgumentException('Username must be at least ' . self::MIN_USERNAME_LENGTH . ' characters');
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new \InvalidArgumentException('Username can only contain letters, numbers, and underscores');
        }
        
        return true;
    }
    
    /**
     * Validate an email address
     * 
     * @param string $email The email to validate
     * @return bool True if valid
     * @throws \InvalidArgumentException If validation fails
     */
    private function validateEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }
        
        return true;
    }
    
    /**
     * Validate a password
     * 
     * @param string $password The password to validate
     * @return bool True if valid
     * @throws \InvalidArgumentException If validation fails
     */
    private function validatePassword(string $password): bool
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new \InvalidArgumentException('Password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters');
        }
        
        return true;
    }
    
    /**
     * Hash a password
     * 
     * @param string $password The password to hash
     * @return string The hashed password
     */
    private function hashPassword(string $password): string
    {
        return password_hash($password, self::PASSWORD_ALGO, [
            'cost' => self::PASSWORD_COST
        ]);
    }
}