<?php

namespace ServerShellBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Repository\ScriptExecutionRepository;

class ScriptExecutionRepositoryTest extends TestCase
{
    public function testRepositoryMethodsExist(): void
    {
        // 验证仓储类实现了预期的查询方法
        $reflection = new \ReflectionClass(ScriptExecutionRepository::class);
        
        $this->assertTrue($reflection->hasMethod('findByNode'));
        $this->assertTrue($reflection->hasMethod('findByScript'));
        $this->assertTrue($reflection->hasMethod('findByStatus'));
        $this->assertTrue($reflection->hasMethod('findByNodeAndScript'));
    }
} 