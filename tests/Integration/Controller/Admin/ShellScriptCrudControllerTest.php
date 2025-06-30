<?php

namespace ServerShellBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Controller\Admin\ShellScriptCrudController;

class ShellScriptCrudControllerTest extends TestCase
{
    public function testInstantiation(): void
    {
        $controller = new ShellScriptCrudController();
        $this->assertInstanceOf(ShellScriptCrudController::class, $controller);
    }
}