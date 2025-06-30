<?php

namespace ServerShellBundle\Tests\Integration\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\TestCase;
use ServerShellBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

class AdminMenuTest extends TestCase
{
    public function testInvoke(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')->willReturn('/admin/list');
        
        $adminMenu = new AdminMenu($linkGenerator);
        
        $menuItem = $this->createMock(ItemInterface::class);
        $childItem = $this->createMock(ItemInterface::class);
        
        $menuItem->method('getChild')->willReturn($childItem);
        $childItem->method('addChild')->willReturnSelf();
        $childItem->method('setUri')->willReturnSelf();
        $childItem->method('setAttribute')->willReturnSelf();
        
        $adminMenu($menuItem);
        
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }
}