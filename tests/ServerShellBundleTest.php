<?php

namespace ServerShellBundle\Tests;

use PHPUnit\Framework\TestCase;
use ServerCommandBundle\ServerCommandBundle;
use ServerNodeBundle\ServerNodeBundle;
use ServerShellBundle\ServerShellBundle;

class ServerShellBundleTest extends TestCase
{
    /**
     * 测试Bundle依赖关系
     */
    public function testBundleDependencies(): void
    {
        $dependencies = ServerShellBundle::getBundleDependencies();
        
        // 验证依赖数量
        $this->assertCount(2, $dependencies);
        
        // 验证ServerNodeBundle依赖
        $this->assertArrayHasKey(ServerNodeBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[ServerNodeBundle::class]);
        
        // 验证ServerCommandBundle依赖
        $this->assertArrayHasKey(ServerCommandBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[ServerCommandBundle::class]);
    }
} 