<?php

namespace ServerShellBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use ServerShellBundle\DependencyInjection\ServerShellExtension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(ServerShellExtension::class)]
final class ServerShellExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testExtensionInstance(): void
    {
        $extension = new ServerShellExtension();

        // 验证扩展实例化正确
        $this->assertInstanceOf(ServerShellExtension::class, $extension);

        // 验证扩展返回正确的别名
        $this->assertEquals('server_shell', $extension->getAlias());
    }
}
