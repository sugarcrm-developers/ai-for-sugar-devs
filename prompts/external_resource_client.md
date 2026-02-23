# ExternalResourceClient Usage Prompt for Sugar MLP Generator

## Critical Issue
**NEVER call `ExternalResourceClient::getInstance()` — this method DOES NOT EXIST in SugarCRM.**

This is a common mistake that breaks generated code. All HTTP requests in SugarCRM custom code must use the proper `ExternalResourceClient` instantiation pattern.

## Official Reference
- **SugarCRM Developer Guide 14.0**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_14.0/integration/externalresourceclient/
- **Key Point**: ExternalResourceClient is instantiated directly, not via a singleton getInstance() method.

## Correct Usage Patterns

### Pattern 1: Direct Constructor Instantiation (Recommended)
```php
<?php
use Sugarcrm\Sugarcrm\Ext\ExternalResourceClient\ExternalResourceClient;

class MyLogicHook {
    public function webhookRequest(array $bean): void
    {
        $client = new ExternalResourceClient();
        $response = $client->request(
            'POST',
            'https://api.example.com/webhook',
            ['json' => $bean]
        );
        // Handle response
    }
}
```

### Pattern 2: Constructor Dependency Injection
```php
<?php
use Sugarcrm\Sugarcrm\Ext\ExternalResourceClient\ExternalResourceClient;

class MyWebhookHandler {
    private ExternalResourceClient $client;
    
    public function __construct(ExternalResourceClient $client = null)
    {
        $this->client = $client ?? new ExternalResourceClient();
    }
    
    public function sendWebhook(array $data): void
    {
        $response = $this->client->request('POST', $this->webhookUrl, [
            'json' => $data,
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }
}
```

### Pattern 3: Lazy Initialization with Type Hint
```php
<?php
use Sugarcrm\Sugarcrm\Ext\ExternalResourceClient\ExternalResourceClient;

class AccountsWebhookHook {
    private ?ExternalResourceClient $client = null;
    
    public function getClient(): ExternalResourceClient
    {
        if ($this->client === null) {
            $this->client = new ExternalResourceClient();
        }
        return $this->client;
    }
    
    public function afterSave(array $bean): void
    {
        $this->getClient()->request('POST', 'https://webhook.service/accounts', [
            'json' => $bean,
            'timeout' => 5,
        ]);
    }
}
```

## INCORRECT Patterns (NEVER USE THESE)

### ❌ WRONG: getInstance() does not exist
```php
<?php
$client = ExternalResourceClient::getInstance();  // FAILS - method doesn't exist
```

### ❌ WRONG: Using curl directly
```php
<?php
$ch = curl_init('https://api.example.com/webhook');  // WRONG - use ExternalResourceClient
curl_exec($ch);
```

### ❌ WRONG: Using file_get_contents with stream context
```php
<?php
$ctx = stream_context_create(['http' => ['method' => 'POST']]);
file_get_contents('https://api.example.com', false, $ctx);  // WRONG - use ExternalResourceClient
```

### ❌ WRONG: Direct fopen
```php
<?php
$fp = fopen('https://api.example.com/webhook', 'r');  // WRONG - use ExternalResourceClient
```

## ExternalResourceClient Methods

The ExternalResourceClient provides the following key methods:

### request(string $method, string $uri, array $options = []): ResponseInterface
Makes an HTTP request and returns a response object.

**Parameters:**
- `$method`: HTTP method ('GET', 'POST', 'PUT', 'DELETE', 'PATCH', etc.)
- `$uri`: Full URL to target endpoint
- `$options`: Request options array (optional)
  - `json`: Array to send as JSON body (auto-encodes)
  - `headers`: Array of HTTP headers
  - `timeout`: Request timeout in seconds (default varies)
  - `query`: Array of query string parameters
  - `body`: Raw body string (if not using 'json')
  - `auth`: [username, password] for basic auth

**Returns:** ResponseInterface (has methods: getStatusCode(), getBody(), getHeader(), etc.)

**Example:**
```php
<?php
$client = new ExternalResourceClient();

// JSON POST with timeout
$response = $client->request('POST', 'https://api.example.com/webhooks', [
    'json' => ['account_id' => '123', 'action' => 'created'],
    'headers' => ['X-API-Key' => 'secret-key'],
    'timeout' => 10,
]);

// Check response
$statusCode = $response->getStatusCode();
if ($statusCode === 200) {
    $body = (string)$response->getBody();
    $data = json_decode($body, true);
}
```

## Common Response Handling

```php
<?php
use Sugarcrm\Sugarcrm\Ext\ExternalResourceClient\ExternalResourceClient;

class WebhookClient {
    public function sendWebhook(string $url, array $payload): bool
    {
        try {
            $client = new ExternalResourceClient();
            $response = $client->request('POST', $url, [
                'json' => $payload,
                'timeout' => 5,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'SugarCRM/14.0',
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode >= 200 && $statusCode < 300) {
                // Success
                return true;
            } elseif ($statusCode === 401) {
                // Unauthorized
                GLOBALS['log']->warning('Webhook auth failed: ' . $url);
                return false;
            } elseif ($statusCode === 404) {
                // Not found
                GLOBALS['log']->warning('Webhook endpoint not found: ' . $url);
                return false;
            } else {
                // Other error
                GLOBALS['log']->error('Webhook request failed: ' . $statusCode);
                return false;
            }
        } catch (\Exception $e) {
            GLOBALS['log']->error('Webhook exception: ' . $e->getMessage());
            return false;
        }
    }
}
```

## Type Hints and Return Types

When using ExternalResourceClient in custom code, always use proper type hints:

```php
<?php
use Sugarcrm\Sugarcrm\Ext\ExternalResourceClient\ExternalResourceClient;
use Psr\Http\Message\ResponseInterface;

class MyWebhookClass {
    private ExternalResourceClient $client;
    
    public function __construct()
    {
        $this->client = new ExternalResourceClient();
    }
    
    // Type hint the return
    public function sendRequest(string $url, array $payload): ResponseInterface
    {
        return $this->client->request('POST', $url, [
            'json' => $payload,
            'timeout' => 10,
        ]);
    }
    
    // Type hint before_save hook parameter
    public function beforeSaveHook(array $bean): void
    {
        $response = $this->sendRequest('https://webhook.local/accounts', $bean);
        
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Webhook failed');
        }
    }
}
```

## Validation Checklist for Generated Code

When generating PHP code that makes HTTP requests:
- [ ] Uses `new ExternalResourceClient()` (constructor call, not getInstance)
- [ ] Never uses `curl_*` functions
- [ ] Never uses `file_get_contents()` for HTTP
- [ ] Never uses `fopen()` for HTTP
- [ ] Never uses `stream_get_contents()` for HTTP
- [ ] Includes proper namespace: `Sugarcrm\Sugarcrm\Ext\ExternalResourceClient\ExternalResourceClient`
- [ ] Has type hints for parameters and return values
- [ ] Handles ResponseInterface properly (getStatusCode(), getBody(), etc.)
- [ ] Includes error handling (try/catch for exceptions)
- [ ] Logs errors using `GLOBALS['log']->error()` or similar
- [ ] Sets reasonable timeouts (e.g., 'timeout' => 5)
- [ ] Validates HTTP status codes in response
- [ ] Uses `json` option for JSON payloads (auto-encodes)
- [ ] Never assumes response is JSON (check Content-Type header)

## Integration with Feature Generator

When generating code that requires HTTP requests:
1. Always use ExternalResourceClient
2. Provide clear examples in comments
3. Include error handling
4. Use typed properties and return types
5. Never use getInstance() or curl
6. Follow the patterns shown above

---

This is an authoritative reference for ExternalResourceClient usage. All generated code using HTTP requests MUST follow these patterns.

