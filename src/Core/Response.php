<?php

namespace LogbieCore;

/**
 * Response Class
 * 
 * A fluent interface for building and sending HTTP responses.
 * Supports JSON, XML, HTML templates, and raw content responses
 * with full control over headers, status codes, and cookies.
 * 
 * @package LogbieCore
 * @since 1.0.0
 */
class Response
{
    /**
     * HTTP status code
     * 
     * @var int
     */
    private int $statusCode = 200;
    
    /**
     * Response headers
     * 
     * @var array<string, string>
     */
    private array $headers = [];
    
    /**
     * Response cookies
     * 
     * @var array<string, array>
     */
    private array $cookies = [];
    
    /**
     * Response content
     * 
     * @var string
     */
    private string $content = '';
    
    /**
     * Content type
     * 
     * @var string
     */
    private string $contentType = 'text/html; charset=UTF-8';
    
    /**
     * Template engine instance
     * 
     * @var ?TemplateEngine
     */
    private ?TemplateEngine $templateEngine = null;
    
    /**
     * Flag indicating if the response has been sent
     * 
     * @var bool
     */
    private bool $sent = false;
    
    /**
     * Constructor
     * 
     * @param TemplateEngine|null $templateEngine The template engine instance
     */
    public function __construct(?TemplateEngine $templateEngine = null)
    {
        $this->templateEngine = $templateEngine;
        
        // Set default headers
        $this->addHeader('X-Content-Type-Options', 'nosniff');
        $this->addHeader('X-Frame-Options', 'DENY');
        $this->addHeader('X-XSS-Protection', '1; mode=block');
    }
    
    /**
     * Set the HTTP status code
     * 
     * @param int $statusCode The HTTP status code
     * @return self
     */
    public function setStatus(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    /**
     * Set the content type
     * 
     * @param string $contentType The content type
     * @return self
     */
    public function setContentType(string $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }
    
    /**
     * Add a header
     * 
     * @param string $name The header name
     * @param string $value The header value
     * @return self
     */
    public function addHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }
    
    /**
     * Set a cookie
     * 
     * @param string $name The cookie name
     * @param string $value The cookie value
     * @param array $options Cookie options (expires, path, domain, secure, httponly, samesite)
     * @return self
     */
    public function setCookie(string $name, string $value, array $options = []): self
    {
        $this->cookies[$name] = [
            'value' => $value,
            'expires' => $options['expires'] ?? 0,
            'path' => $options['path'] ?? '/',
            'domain' => $options['domain'] ?? null,
            'secure' => $options['secure'] ?? true,
            'httponly' => $options['httponly'] ?? true,
            'samesite' => $options['samesite'] ?? 'Strict'
        ];
        
        return $this;
    }
    
    /**
     * Remove a cookie
     * 
     * @param string $name The cookie name
     * @return self
     */
    public function removeCookie(string $name): self
    {
        return $this->setCookie($name, '', [
            'expires' => time() - 3600,
            'path' => '/'
        ]);
    }
    
    /**
     * Set the response content
     * 
     * @param string $content The content
     * @param string|null $contentType The content type (null to keep current)
     * @return self
     */
    public function setContent(string $content, ?string $contentType = null): self
    {
        $this->content = $content;
        
        if ($contentType !== null) {
            $this->setContentType($contentType);
        }
        
        return $this;
    }
    
    /**
     * Append content to the response
     * 
     * @param string $content The content to append
     * @return self
     */
    public function appendContent(string $content): self
    {
        $this->content .= $content;
        return $this;
    }
    
    /**
     * Set JSON content
     * 
     * @param mixed $data The data to encode as JSON
     * @return self
     * @throws \RuntimeException If JSON encoding fails
     */
    public function setJson(mixed $data): self
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if ($json === false) {
            throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }
        
        return $this->setContent($json, 'application/json');
    }
    
    /**
     * Set XML content
     * 
     * @param array $data The data to encode as XML
     * @param string $rootElement The root element name
     * @return self
     */
    public function setXml(array $data, string $rootElement = 'root'): self
    {
        $xml = $this->arrayToXml($data, $rootElement);
        return $this->setContent($xml, 'application/xml');
    }
    
    /**
     * Render a template
     * 
     * @param string $template The template path
     * @param array $data The template data
     * @return self
     * @throws \RuntimeException If the template engine is not set
     */
    public function render(string $template, array $data = []): self
    {
        if ($this->templateEngine === null) {
            throw new \RuntimeException('Template engine not initialized');
        }
        
        $content = $this->templateEngine->render($template, $data);
        return $this->setContent($content, 'text/html; charset=UTF-8');
    }
    
    /**
     * Send the response
     * 
     * @return void
     */
    public function send(): void
    {
        if ($this->sent) {
            return;
        }
        
        // Set status code
        http_response_code($this->statusCode);
        
        // Set content type
        header('Content-Type: ' . $this->contentType);
        
        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        // Set cookies
        foreach ($this->cookies as $name => $options) {
            setcookie(
                $name,
                $options['value'],
                [
                    'expires' => $options['expires'],
                    'path' => $options['path'],
                    'domain' => $options['domain'],
                    'secure' => $options['secure'],
                    'httponly' => $options['httponly'],
                    'samesite' => $options['samesite']
                ]
            );
        }
        
        // Send content
        echo $this->content;
        
        $this->sent = true;
    }
    
    /**
     * Convert an array to XML
     * 
     * @param array $data The data to convert
     * @param string $rootElement The root element name
     * @param \DOMDocument|null $document The DOM document
     * @param \DOMElement|null $parent The parent element
     * @return string The XML string
     */
    private function arrayToXml(
        array $data,
        string $rootElement = 'root',
        ?\DOMDocument $document = null,
        ?\DOMElement $parent = null
    ): string {
        // Create new document if not provided
        if ($document === null) {
            $document = new \DOMDocument('1.0', 'UTF-8');
            $document->formatOutput = true;
            $parent = $document->createElement($rootElement);
            $document->appendChild($parent);
        }
        
        // Process each data item
        foreach ($data as $key => $value) {
            // Handle numeric keys for lists
            if (is_numeric($key)) {
                $key = 'item';
            }
            
            // Create element
            $element = $document->createElement($key);
            $parent->appendChild($element);
            
            // Process value based on type
            if (is_array($value)) {
                // Recursively process arrays
                $this->arrayToXml($value, $rootElement, $document, $element);
            } else {
                // Set text content for scalar values
                $element->appendChild($document->createTextNode((string) $value));
            }
        }
        
        // Return XML string if this is the root call
        if ($rootElement === $parent->nodeName) {
            return $document->saveXML();
        }
        
        return '';
    }
    
    /**
     * Check if the response has been sent
     * 
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent;
    }
    
    /**
     * Get the current status code
     * 
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * Get the current content type
     * 
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }
    
    /**
     * Get the current content
     * 
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
    
    /**
     * Set the template engine
     * 
     * @param TemplateEngine $templateEngine The template engine
     * @return self
     */
    public function setTemplateEngine(TemplateEngine $templateEngine): self
    {
        $this->templateEngine = $templateEngine;
        return $this;
    }
}