# Server Shell Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/server-shell-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/server-shell-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/server-shell-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/server-shell-bundle)
[![License](https://img.shields.io/packagist/l/tourze/server-shell-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/server-shell-bundle)
[![Build Status](https://img.shields.io/travis/tourze/php-monorepo/master.svg?style=flat-square)](
https://travis-ci.org/tourze/php-monorepo)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/php-monorepo.svg?style=flat-square)](
https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/php-monorepo.svg?style=flat-square)](
https://scrutinizer-ci.com/g/tourze/php-monorepo)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/server-shell-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/server-shell-bundle)

A powerful Symfony bundle for managing and executing shell scripts on remote servers. 
This bundle provides a comprehensive system for creating, managing, and executing shell 
scripts across multiple nodes with full execution tracking and asynchronous processing support.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [API Reference](#api-reference)
- [Advanced Usage](#advanced-usage)
- [Admin Interface](#admin-interface)
- [Security Considerations](#security-considerations)
- [Dependencies](#dependencies)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Script Management**: Create, update, and manage shell scripts with metadata
- **Remote Execution**: Execute scripts on remote nodes via SSH
- **Execution Tracking**: Track script execution status, results, and performance metrics
- **Asynchronous Processing**: Support for async script execution using Symfony Messenger
- **Security**: Configurable sudo execution and script validation
- **EasyAdmin Integration**: Built-in admin interface for script management
- **History & Logging**: Complete execution history with detailed logging
- **Flexible Configuration**: Configurable working directories, timeouts, and execution parameters
- **Tag-based Organization**: Organize scripts with tags for easy categorization

## Installation

```bash
composer require tourze/server-shell-bundle
```

## Configuration

### Bundle Registration

Register the bundle in your `config/bundles.php`:

```php
return [
    // ... other bundles
    ServerShellBundle\ServerShellBundle::class => ['all' => true],
];
```

### Database Setup

Run migrations to create the required database tables:

```bash
php bin/console doctrine:migrations:migrate
```

### Script Parameters

- **name**: Human-readable script name
- **content**: Shell script content
- **workingDirectory**: Directory to execute the script in (default: /tmp)
- **useSudo**: Whether to execute with sudo privileges (default: false)
- **timeout**: Execution timeout in seconds (default: 300)
- **tags**: Array of tags for organization
- **description**: Script description
- **enabled**: Whether the script is enabled for execution (default: true)

### Execution Tracking

Each script execution creates a `ScriptExecution` entity that tracks:

- Execution status (PENDING, RUNNING, COMPLETED, FAILED, TIMEOUT, CANCELED)
- Execution results and output
- Execution time and performance metrics
- Associated node and script information
- Exit codes and error information

## Quick Start

### Basic Usage

```php
<?php

use ServerShellBundle\Service\ShellScriptService;
use ServerNodeBundle\Entity\Node;

// Inject the service
public function __construct(
    private ShellScriptService $shellScriptService
) {}

// Create a new script
$script = $this->shellScriptService->createScript(
    name: 'System Update',
    content: "#!/bin/bash\napt update && apt upgrade -y",
    workingDirectory: '/tmp',
    useSudo: true,
    timeout: 300,
    tags: ['system', 'update'],
    description: 'Updates system packages'
);

// Execute script on a node
$node = $this->nodeRepository->find(1);
$execution = $this->shellScriptService->executeScript($script, $node);

// Check execution status
echo "Status: " . $execution->getStatus()->value;
echo "Result: " . $execution->getResult();
```

### Asynchronous Execution

```php
// Schedule script for async execution
$execution = $this->shellScriptService->scheduleScript($script, $node);

// The script will be executed asynchronously via Symfony Messenger
// Check status later using the execution ID
$execution = $this->shellScriptService->findExecutionById($execution->getId());
```

## API Reference

### ShellScriptService

The main service for script management:

```php
// Create script
createScript(string $name, string $content, ...): ShellScript

// Update script
updateScript(ShellScript $script, ...): ShellScript

// Execute script
executeScript(ShellScript $script, Node $node): ScriptExecution

// Schedule async execution
scheduleScript(ShellScript $script, Node $node): ScriptExecution

// Find scripts
findScriptById(int $id): ?ShellScript
findAllEnabledScripts(): array
findScriptsByTags(array $tags): array

// Find executions
findExecutionById(int $id): ?ScriptExecution
findExecutionsByNode(Node $node): array
findExecutionsByScript(ShellScript $script): array
```

### Entities

- **ShellScript**: Represents a shell script with metadata
- **ScriptExecution**: Tracks individual script executions
- **CommandStatus**: Enum for execution status values

## Advanced Usage

### Batch Script Execution

```php
// Execute multiple scripts on multiple nodes
$scripts = $this->shellScriptService->findScriptsByTags(['deployment']);
$nodes = $this->nodeRepository->findByEnvironment('production');

foreach ($scripts as $script) {
    foreach ($nodes as $node) {
        $this->shellScriptService->scheduleScript($script, $node);
    }
}
```

### Custom Script Templates

```php
// Create script from template
$script = $this->shellScriptService->createScript(
    name: 'Database Backup',
    content: $this->renderTemplate('database_backup.sh.twig', [
        'database' => 'app_prod',
        'backup_path' => '/backups'
    ])
);
```

### Execution Monitoring

```php
// Monitor execution progress
$execution = $this->shellScriptService->findExecutionById($id);

if ($execution->getStatus() === CommandStatus::RUNNING) {
    echo "Execution time: " . $execution->getExecutionTime() . "s";
} elseif ($execution->getStatus() === CommandStatus::COMPLETED) {
    echo "Completed in: " . $execution->getExecutionTime() . "s";
    echo "Exit code: " . $execution->getExitCode();
}
```

## Admin Interface

The bundle provides EasyAdmin controllers for managing scripts and viewing execution history:

- **ShellScriptCrudController**: Manage shell scripts
- **ScriptExecutionCrudController**: View execution history and results

## Security Considerations

- Scripts are temporarily stored in `/tmp/shell_scripts` with restricted permissions (0700)
- Remote script files are automatically cleaned up after execution
- Sudo execution is configurable per script
- All executions are logged for audit purposes

## Dependencies

This bundle depends on:

- `tourze/server-node-bundle`: For remote node management
- `tourze/server-command-bundle`: For remote command execution
- `symfony/messenger`: For asynchronous processing
- `easycorp/easyadmin-bundle`: For admin interface

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.