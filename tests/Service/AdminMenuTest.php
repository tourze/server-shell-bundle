<?php

namespace ServerShellBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerShellBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses] final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 无需特殊设置
    }

    public function testServiceInstance(): void
    {
        // 从容器中获取 AdminMenu 服务
        $adminMenu = self::getService(AdminMenu::class);

        // 测试 AdminMenu 能够正常实例化
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);

        // 验证构造函数参数正确设置
        $reflection = new \ReflectionClass($adminMenu);
        $property = $reflection->getProperty('linkGenerator');
        $property->setAccessible(true);
        $linkGenerator = $property->getValue($adminMenu);

        $this->assertInstanceOf(LinkGeneratorInterface::class, $linkGenerator);
    }
}
