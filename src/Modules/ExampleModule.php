<?php

namespace Logbie;

use LogbieCore\BaseModule;

/**
 * Example Module
 * 
 * A simple example module demonstrating the Logbie Framework.
 * 
 * @package Logbie
 * @since 1.0.0
 */
class ExampleModule extends BaseModule
{
    /**
     * Run the module
     * 
     * @param array $arguments Arguments passed to the module
     * @return mixed The result of the module execution
     */
    public function run(array $arguments = []): mixed
    {
        try {
            // Get the action from the arguments
            $action = $arguments[0] ?? 'default';
            
            // Log the action
            $this->logger->log("ExampleModule: Running action '{$action}'");
            
            // Route to the appropriate method based on the action
            return match ($action) {
                'hello' => $this->helloAction($arguments[1] ?? 'World'),
                'json' => $this->jsonAction(),
                'form' => $this->formAction(),
                'db' => $this->dbAction(),
                'template' => $this->templateAction(),
                default => $this->defaultAction(),
            };
        } catch (\Exception $e) {
            // Log the error
            $this->logger->log("Error in ExampleModule: " . $e->getMessage());
            
            // Send an error response
            return $this->sendError($e->getMessage(), 500);
        }
    }
    
    /**
     * Default action
     * 
     * @return void
     */
    private function defaultAction(): void
    {
        $this->response->setContent(
            '<h1>Logbie Framework Example Module</h1>' .
            '<p>This is a simple example module demonstrating the Logbie Framework.</p>' .
            '<ul>' .
            '<li><a href="/example/hello">Hello World</a></li>' .
            '<li><a href="/example/hello/User">Hello User</a></li>' .
            '<li><a href="/example/json">JSON Response</a></li>' .
            '<li><a href="/example/form">Form Example</a></li>' .
            '<li><a href="/example/db">Database Example</a></li>' .
            '<li><a href="/example/template">Template Example</a></li>' .
            '</ul>'
        )->send();
    }
    
    /**
     * Hello action
     * 
     * @param string $name The name to greet
     * @return void
     */
    private function helloAction(string $name): void
    {
        $this->response->setContent(
            '<h1>Hello, ' . htmlspecialchars($name) . '!</h1>' .
            '<p><a href="/example">Back to Examples</a></p>'
        )->send();
    }
    
    /**
     * JSON action
     * 
     * @return void
     */
    private function jsonAction(): void
    {
        $data = [
            'message' => 'Hello from the Logbie Framework!',
            'timestamp' => time(),
            'framework' => 'Logbie',
            'module' => 'ExampleModule',
            'action' => 'json'
        ];
        
        $this->response->setJson($data)->send();
    }
    
    /**
     * Form action
     * 
     * @return void
     */
    private function formAction(): void
    {
        // Check if this is a POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the form data
            $name = $_POST['name'] ?? 'Unknown';
            $email = $_POST['email'] ?? 'No email provided';
            
            // Validate the data
            if (empty($name) || empty($email)) {
                return $this->sendError('Name and email are required', 400);
            }
            
            // Process the form
            $this->logger->log("Form submitted: Name: {$name}, Email: {$email}");
            
            // Send a success response
            return $this->sendSuccess(
                ['name' => $name, 'email' => $email],
                'Form submitted successfully'
            );
        }
        
        // Display the form
        $this->response->setContent(
            '<h1>Form Example</h1>' .
            '<form method="post" action="/example/form">' .
            '<div>' .
            '<label for="name">Name:</label>' .
            '<input type="text" id="name" name="name" required>' .
            '</div>' .
            '<div>' .
            '<label for="email">Email:</label>' .
            '<input type="email" id="email" name="email" required>' .
            '</div>' .
            '<div>' .
            '<button type="submit">Submit</button>' .
            '</div>' .
            '</form>' .
            '<p><a href="/example">Back to Examples</a></p>'
        )->send();
    }
    
    /**
     * Database action
     * 
     * @return void
     */
    private function dbAction(): void
    {
        try {
            // Check if the users table exists
            try {
                $schema = $this->db->getTableSchema('users');
            } catch (\RuntimeException $e) {
                // Create the users table if it doesn't exist
                $this->db->query('
                    CREATE TABLE IF NOT EXISTS users (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        username VARCHAR(255) NOT NULL UNIQUE,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        password_hash VARCHAR(255) NOT NULL,
                        active BOOLEAN NOT NULL DEFAULT TRUE,
                        email_verified BOOLEAN NOT NULL DEFAULT FALSE,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                        last_login TIMESTAMP NULL DEFAULT NULL
                    )
                ');
                
                // Add a sample user
                $this->db->create('users', [
                    'username' => 'example_user',
                    'email' => 'example@example.com',
                    'password_hash' => password_hash('password', PASSWORD_BCRYPT),
                    'active' => true,
                    'email_verified' => false,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                $this->logger->log('Created users table and added a sample user');
            }
            
            // Get all users
            $users = $this->db->read('users');
            
            // Display the users
            $html = '<h1>Database Example</h1>';
            $html .= '<h2>Users</h2>';
            
            if (empty($users)) {
                $html .= '<p>No users found.</p>';
            } else {
                $html .= '<table border="1">';
                $html .= '<tr><th>ID</th><th>Username</th><th>Email</th><th>Active</th><th>Created</th></tr>';
                
                foreach ($users as $user) {
                    $html .= '<tr>';
                    $html .= '<td>' . htmlspecialchars($user['id']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($user['username']) . '</td>';
                    $html .= '<td>' . htmlspecialchars($user['email']) . '</td>';
                    $html .= '<td>' . ($user['active'] ? 'Yes' : 'No') . '</td>';
                    $html .= '<td>' . htmlspecialchars($user['created_at']) . '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '</table>';
            }
            
            $html .= '<p><a href="/example">Back to Examples</a></p>';
            
            $this->response->setContent($html)->send();
        } catch (\Exception $e) {
            return $this->sendError('Database error: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Template action
     * 
     * @return void
     */
    private function templateAction(): void
    {
        try {
            // Create a templates directory if it doesn't exist
            $templateDir = dirname(__DIR__, 2) . '/templates';
            if (!is_dir($templateDir)) {
                mkdir($templateDir, 0755, true);
            }
            
            // Create a template file if it doesn't exist
            $templateFile = $templateDir . '/example.html';
            if (!file_exists($templateFile)) {
                $templateContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{{{title}}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .user { border: 1px solid #ccc; padding: 10px; margin-bottom: 10px; }
        .username { font-weight: bold; }
        .email { color: #666; }
    </style>
</head>
<body>
    <h1>{{{title}}}</h1>
    
    <p>Welcome to the Logbie Framework template example!</p>
    
    {% if users %}
        <h2>Users</h2>
        {% while users %}
            <div class="user">
                <div class="username">{{{user.username}}}</div>
                <div class="email">{{{user.email}}}</div>
            </div>
        {% end while %}
    {% else %}
        <p>No users found.</p>
    {% endif %}
    
    <p><a href="/example">Back to Examples</a></p>
</body>
</html>
HTML;
                file_put_contents($templateFile, $templateContent);
            }
            
            // Get all users
            $users = $this->db->read('users');
            
            // Render the template
            $this->response->render('example.html', [
                'title' => 'Template Example',
                'users' => $users
            ])->send();
        } catch (\Exception $e) {
            return $this->sendError('Template error: ' . $e->getMessage(), 500);
        }
    }
}