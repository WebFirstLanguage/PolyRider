# SOLID Principles Guide for Logbie Framework

## Overview of SOLID Principles

SOLID is an acronym for five design principles that help make software maintainable and scalable:
- **S**ingle Responsibility Principle (SRP)
- **O**pen-Closed Principle (OCP)
- **L**iskov Substitution Principle (LSP)
- **I**nterface Segregation Principle (ISP)
- **D**ependency Inversion Principle (DIP)

## 1. Single Responsibility Principle (SRP)
"A class should have one, and only one, reason to change."

### Bad Example:
```php
// classes/User/User.php
namespace Classes\User;

class User {
    private $db;
    
    public function getUserData($id) {
        return $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
    }
    
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function sendPasswordResetEmail($email) {
        $mailer = new \PHPMailer();
        // Email sending logic here
    }
}
```

### Good Example:
```php
// classes/User/UserRepository.php
namespace Classes\User;

class UserRepository {
    private $db;
    
    public function findById($id): ?User {
        return $this->db->query("SELECT * FROM users WHERE id = ?", [$id]);
    }
}

// classes/Utility/EmailValidator.php
namespace Classes\Utility;

class EmailValidator {
    public function isValid(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

// classes/Services/PasswordResetService.php
namespace Classes\Services;

class PasswordResetService {
    private $mailer;
    
    public function sendResetEmail(string $email): void {
        // Email sending logic here
    }
}
```

## 2. Open-Closed Principle (OCP)
"Software entities should be open for extension but closed for modification."

### Bad Example:
```php
// classes/Payment/PaymentProcessor.php
namespace Classes\Payment;

class PaymentProcessor {
    public function processPayment($type, $amount) {
        if ($type === 'credit') {
            // Process credit card payment
        } else if ($type === 'paypal') {
            // Process PayPal payment
        }
        // Adding new payment types requires modifying this class
    }
}
```

### Good Example:
```php
// classes/Payment/PaymentMethodInterface.php
namespace Classes\Payment;

interface PaymentMethodInterface {
    public function process(float $amount): bool;
}

// classes/Payment/CreditCardPayment.php
class CreditCardPayment implements PaymentMethodInterface {
    public function process(float $amount): bool {
        // Process credit card payment
        return true;
    }
}

// classes/Payment/PayPalPayment.php
class PayPalPayment implements PaymentMethodInterface {
    public function process(float $amount): bool {
        // Process PayPal payment
        return true;
    }
}

// classes/Payment/PaymentProcessor.php
class PaymentProcessor {
    public function processPayment(PaymentMethodInterface $paymentMethod, float $amount): bool {
        return $paymentMethod->process($amount);
    }
}
```

## 3. Liskov Substitution Principle (LSP)
"Derived classes must be substitutable for their base classes."

### Bad Example:
```php
// classes/Storage/FileStorage.php
namespace Classes\Storage;

class FileStorage {
    public function save(string $data): bool {
        // Save to file
        return true;
    }
}

class ReadOnlyStorage extends FileStorage {
    public function save(string $data): bool {
        throw new \Exception('Cannot save to read-only storage');
    }
}
```

### Good Example:
```php
// classes/Storage/StorageInterface.php
namespace Classes\Storage;

interface StorageInterface {
    public function read(string $key): string;
}

interface WritableStorageInterface extends StorageInterface {
    public function save(string $key, string $data): bool;
}

// classes/Storage/FileStorage.php
class FileStorage implements WritableStorageInterface {
    public function read(string $key): string {
        // Read from file
        return $data;
    }
    
    public function save(string $key, string $data): bool {
        // Save to file
        return true;
    }
}

// classes/Storage/ReadOnlyStorage.php
class ReadOnlyStorage implements StorageInterface {
    public function read(string $key): string {
        // Read from storage
        return $data;
    }
}
```

## 4. Interface Segregation Principle (ISP)
"Clients should not be forced to depend on interfaces they do not use."

### Bad Example:
```php
// classes/Repository/UserRepositoryInterface.php
namespace Classes\Repository;

interface UserRepositoryInterface {
    public function find($id);
    public function save(User $user);
    public function delete($id);
    public function sendEmail(User $user);
    public function generateReport(User $user);
}
```

### Good Example:
```php
// classes/Repository/UserRepositoryInterface.php
namespace Classes\Repository;

interface UserRepositoryInterface {
    public function find($id);
    public function save(User $user);
    public function delete($id);
}

// classes/Services/UserNotificationInterface.php
interface UserNotificationInterface {
    public function sendEmail(User $user);
}

// classes/Reporting/UserReportInterface.php
interface UserReportInterface {
    public function generateReport(User $user);
}

// classes/Repository/UserRepository.php
class UserRepository implements UserRepositoryInterface {
    // Implements only repository methods
}

// classes/Services/UserNotificationService.php
class UserNotificationService implements UserNotificationInterface {
    // Implements only notification methods
}
```

## 5. Dependency Inversion Principle (DIP)
"High-level modules should not depend on low-level modules. Both should depend on abstractions."

### Bad Example:
```php
// classes/Order/OrderProcessor.php
namespace Classes\Order;

class OrderProcessor {
    private $mysqlDatabase;
    
    public function __construct() {
        $this->mysqlDatabase = new MySQLDatabase();
    }
    
    public function process(Order $order) {
        $this->mysqlDatabase->save($order);
    }
}
```

### Good Example:
```php
// classes/Database/DatabaseInterface.php
namespace Classes\Database;

interface DatabaseInterface {
    public function save($data): bool;
}

// classes/Order/OrderProcessor.php
namespace Classes\Order;

class OrderProcessor {
    private $database;
    
    public function __construct(DatabaseInterface $database) {
        $this->database = $database;
    }
    
    public function process(Order $order) {
        return $this->database->save($order);
    }
}
```

## Implementing SOLID in Modules

### Example Module Following SOLID:
```php
// modules/OrderManager.php
namespace Logbie;

use core\BaseModule;
use Classes\Order\OrderProcessor;
use Classes\Order\OrderValidator;
use Classes\Payment\PaymentMethodInterface;

class OrderManager extends BaseModule {
    private $orderProcessor;
    private $orderValidator;
    private $paymentMethod;
    
    public function __construct($db, $container = null) {
        parent::__construct($db, $container);
        
        // Dependencies are injected and follow interfaces
        $this->orderProcessor = $container->get('orderProcessor');
        $this->orderValidator = $container->get('orderValidator');
        $this->paymentMethod = $container->get('paymentMethod');
    }
    
    public function run(array $arguments = []) {
        $action = $arguments[0] ?? 'default';
        
        switch ($action) {
            case 'process':
                $this->processOrder();
                break;
            default:
                $this->defaultAction();
        }
    }
    
    private function processOrder() {
        try {
            $orderData = json_decode(file_get_contents('php://input'), true);
            
            if (!$this->orderValidator->validate($orderData)) {
                throw new \InvalidArgumentException('Invalid order data');
            }
            
            $order = $this->orderProcessor->process($orderData);
            $payment = $this->paymentMethod->process($order->getTotal());
            
            $this->response->setJson([
                'error' => false,
                'message' => 'Order processed successfully',
                'orderId' => $order->getId()
            ])->send();
            
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
            $this->response->setStatus(400)
                ->setJson([
                    'error' => true,
                    'message' => $e->getMessage()
                ])->send();
        }
    }
}
```

## Best Practices for SOLID Implementation

1. **Dependency Injection**
   - Use the Container class for managing dependencies
   - Inject dependencies through constructors
   - Type-hint interfaces rather than concrete classes

2. **Interface Design**
   - Create small, focused interfaces
   - Use interface inheritance for related functionality
   - Place interfaces in appropriate namespace directories

3. **Module Organization**
   - Keep modules focused on specific business domains
   - Use services for complex business logic
   - Implement validation in separate classes

4. **Testing**
   - Write unit tests for each class
   - Mock dependencies using interfaces
   - Test each SOLID principle separately

5. **Error Handling**
   - Create specific exception classes
   - Handle errors at appropriate levels
   - Log errors consistently

