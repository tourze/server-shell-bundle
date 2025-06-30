<?php

namespace ServerShellBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\DependencyInjection\ServerShellExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ServerShellExtensionTest extends TestCase
{
    public function testLoad(): void
    {
        $extension = new ServerShellExtension();
        $container = new ContainerBuilder();
        
        $extension->load([], $container);
        
        // 验证资源已被加载（通过检查是否有被自动发现的服务）
        $this->assertTrue($container->hasDefinition('ServerShellBundle\Service\ShellScriptService'));
        $this->assertTrue($container->hasDefinition('ServerShellBundle\Service\AdminMenu'));
        $this->assertTrue($container->hasDefinition('ServerShellBundle\Repository\ShellScriptRepository'));
        $this->assertTrue($container->hasDefinition('ServerShellBundle\Repository\ScriptExecutionRepository'));
    }
}