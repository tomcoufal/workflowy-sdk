# Workflowy PHP SDK

A modern, type-safe PHP SDK for the Workflowy API, designed for Laravel 12+ and PHP 8.2+.

## Features

- 🚀 **Modern PHP 8.2+**: Uses `readonly` classes, Enums, and strict typing.
- 🧩 **DTOs**: Returns structured objects (`NodeData`, `TargetData`) instead of arrays.
- 🔌 **PSR Compliant**: Built on PSR-18 (HTTP Client) and PSR-17 (HTTP Factories).
- ⚡ **Laravel Integration**: Includes a Service Provider and Facade out of the box.

## Installation

```bash
composer require tomcoufal/workflowy-sdk
```

## Configuration (Laravel)

Add your API key to your `.env` file:

```env
WORKFLOWY_API_KEY=your_secret_api_key
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=workflowy-config
```

## Usage

### Basic Usage (Generic PHP)

```php
use Workflowy\WorkflowyClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

$client = new WorkflowyClient(
    apiKey: 'your-api-key',
    httpClient: new Client(),
    requestFactory: new HttpFactory(),
    streamFactory: new HttpFactory()
);

$nodes = $client->nodes()->get('node-id');
```

### Usage in Laravel

Use the Facade for clean, expressive syntax:

```php
use Workflowy\Laravel\Facades\Workflowy;
use Workflowy\Enums\LayoutMode;

// 1. Get the entire tree or a specific node
$node = Workflowy::nodes()->get();

// 2. Create a new node
$newItem = Workflowy::nodes()->create(
    parentId: $node->id,
    name: 'My New Task',
    priority: 1,
    note: '#important'
);

// 3. Mark as completed
Workflowy::nodes()->check($newItem->id);

// 4. List targets/shortcuts
$targets = Workflowy::targets()->list();
```

## Data Transfer Objects (DTOs)

The SDK returns `readonly` objects to ensure data integrity and IDE autocompletion.

```php
// NodeData
echo $node->name;        // string
echo $node->isCompleted; // bool
echo $node->createdAt->format('Y-m-d'); // DateTimeImmutable
```

## License

MIT
