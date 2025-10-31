# Server Shell Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![最新版本](https://img.shields.io/packagist/v/tourze/server-shell-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/server-shell-bundle)
[![下载量](https://img.shields.io/packagist/dt/tourze/server-shell-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/server-shell-bundle)
[![许可证](https://img.shields.io/packagist/l/tourze/server-shell-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/server-shell-bundle)
[![PHP 版本](https://img.shields.io/packagist/php-v/tourze/server-shell-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/server-shell-bundle)
[![构建状态](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![代码覆盖率](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

一个强大的 Symfony 包，用于管理和执行远程服务器上的 Shell 脚本。
该包提供了一个完整的系统，用于创建、管理和执行 Shell 脚本，支持多节点执行、
完整的执行跟踪和异步处理。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置说明](#配置说明)
- [快速开始](#快速开始)
- [API 参考](#api-参考)
- [高级用法](#高级用法)
- [后台管理界面](#后台管理界面)
- [安全考虑](#安全考虑)
- [依赖项](#依赖项)
- [贡献指南](#贡献指南)
- [许可证](#许可证)

## 功能特性

- **脚本管理**：创建、更新和管理带有元数据的 Shell 脚本
- **远程执行**：通过 SSH 在远程节点上执行脚本
- **执行跟踪**：跟踪脚本执行状态、结果和性能指标
- **异步处理**：使用 Symfony Messenger 支持异步脚本执行
- **安全性**：可配置的 sudo 执行和脚本验证
- **EasyAdmin 集成**：内置的脚本管理后台界面
- **历史记录和日志**：完整的执行历史记录和详细日志
- **灵活配置**：可配置的工作目录、超时时间和执行参数
- **标签组织**：使用标签组织脚本，便于分类管理

## 安装

```bash
composer require tourze/server-shell-bundle
```

## 配置说明

### Bundle 注册

在你的 `config/bundles.php` 中注册该包：

```php
return [
    // ... 其他包
    ServerShellBundle\ServerShellBundle::class => ['all' => true],
];
```

### 数据库设置

运行迁移以创建所需的数据库表：

```bash
php bin/console doctrine:migrations:migrate
```

### 脚本参数

- **name**: 可读的脚本名称
- **content**: Shell 脚本内容
- **workingDirectory**: 执行脚本的目录（默认：/tmp）
- **useSudo**: 是否使用 sudo 权限执行（默认：false）
- **timeout**: 执行超时时间（秒）（默认：300）
- **tags**: 用于组织的标签数组
- **description**: 脚本描述
- **enabled**: 脚本是否启用执行（默认：true）

### 执行跟踪

每个脚本执行都会创建一个 `ScriptExecution` 实体，跟踪：

- 执行状态（PENDING、RUNNING、COMPLETED、FAILED、TIMEOUT、CANCELED）
- 执行结果和输出
- 执行时间和性能指标
- 关联的节点和脚本信息
- 退出代码和错误信息

## 快速开始

### 基本使用

```php
<?php

use ServerShellBundle\Service\ShellScriptService;
use ServerNodeBundle\Entity\Node;

// 注入服务
public function __construct(
    private ShellScriptService $shellScriptService
) {}

// 创建新脚本
$script = $this->shellScriptService->createScript(
    name: '系统更新',
    content: "#!/bin/bash\napt update && apt upgrade -y",
    workingDirectory: '/tmp',
    useSudo: true,
    timeout: 300,
    tags: ['system', 'update'],
    description: '更新系统包'
);

// 在节点上执行脚本
$node = $this->nodeRepository->find(1);
$execution = $this->shellScriptService->executeScript($script, $node);

// 检查执行状态
echo "状态: " . $execution->getStatus()->value;
echo "结果: " . $execution->getResult();
```

### 异步执行

```php
// 安排脚本异步执行
$execution = $this->shellScriptService->scheduleScript($script, $node);

// 脚本将通过 Symfony Messenger 异步执行
// 稍后使用执行 ID 检查状态
$execution = $this->shellScriptService->findExecutionById($execution->getId());
```

## API 参考

### ShellScriptService

脚本管理的主要服务：

```php
// 创建脚本
createScript(string $name, string $content, ...): ShellScript

// 更新脚本
updateScript(ShellScript $script, ...): ShellScript

// 执行脚本
executeScript(ShellScript $script, Node $node): ScriptExecution

// 安排异步执行
scheduleScript(ShellScript $script, Node $node): ScriptExecution

// 查找脚本
findScriptById(int $id): ?ShellScript
findAllEnabledScripts(): array
findScriptsByTags(array $tags): array

// 查找执行记录
findExecutionById(int $id): ?ScriptExecution
findExecutionsByNode(Node $node): array
findExecutionsByScript(ShellScript $script): array
```

### 实体类

- **ShellScript**: 表示带有元数据的 Shell 脚本
- **ScriptExecution**: 跟踪单个脚本执行
- **CommandStatus**: 执行状态值的枚举

## 高级用法

### 批量脚本执行

```php
// 在多个节点上执行多个脚本
$scripts = $this->shellScriptService->findScriptsByTags(['deployment']);
$nodes = $this->nodeRepository->findByEnvironment('production');

foreach ($scripts as $script) {
    foreach ($nodes as $node) {
        $this->shellScriptService->scheduleScript($script, $node);
    }
}
```

### 自定义脚本模板

```php
// 从模板创建脚本
$script = $this->shellScriptService->createScript(
    name: '数据库备份',
    content: $this->renderTemplate('database_backup.sh.twig', [
        'database' => 'app_prod',
        'backup_path' => '/backups'
    ])
);
```

### 执行监控

```php
// 监控执行进度
$execution = $this->shellScriptService->findExecutionById($id);

if ($execution->getStatus() === CommandStatus::RUNNING) {
    echo "执行时间: " . $execution->getExecutionTime() . "秒";
} elseif ($execution->getStatus() === CommandStatus::COMPLETED) {
    echo "完成时间: " . $execution->getExecutionTime() . "秒";
    echo "退出代码: " . $execution->getExitCode();
}
```

## 后台管理界面

该包提供了 EasyAdmin 控制器用于管理脚本和查看执行历史：

- **ShellScriptCrudController**: 管理 Shell 脚本
- **ScriptExecutionCrudController**: 查看执行历史和结果

## 安全考虑

- 脚本临时存储在 `/tmp/shell_scripts` 中，具有受限权限（0700）
- 远程脚本文件在执行后会自动清理
- 每个脚本可配置 sudo 执行
- 所有执行都会记录日志用于审计

## 依赖项

该包依赖于：

- `tourze/server-node-bundle`: 用于远程节点管理
- `tourze/server-command-bundle`: 用于远程命令执行
- `symfony/messenger`: 用于异步处理
- `easycorp/easyadmin-bundle`: 用于后台管理界面

## 贡献指南

详情请参阅 [CONTRIBUTING.md](CONTRIBUTING.md)。

## 许可证

MIT 许可证。详情请参阅 [许可证文件](LICENSE)。