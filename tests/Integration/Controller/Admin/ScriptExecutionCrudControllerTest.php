<?php

namespace ServerShellBundle\Tests\Integration\Controller\Admin;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Controller\Admin\ScriptExecutionCrudController;

class ScriptExecutionCrudControllerTest extends TestCase
{
    public function testInstantiation(): void
    {
        $controller = new ScriptExecutionCrudController();
        $this->assertInstanceOf(ScriptExecutionCrudController::class, $controller);
    }
}