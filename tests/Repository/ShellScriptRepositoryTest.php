<?php

namespace ServerShellBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Repository\ShellScriptRepository;

class ShellScriptRepositoryTest extends TestCase
{
    public function testRepositoryMethodsExist(): void
    {
        // 验证仓储类实现了预期的查询方法
        $reflection = new \ReflectionClass(ShellScriptRepository::class);
        
        $this->assertTrue($reflection->hasMethod('findByTags'));
        $this->assertTrue($reflection->hasMethod('findAllEnabled'));
    }
} 