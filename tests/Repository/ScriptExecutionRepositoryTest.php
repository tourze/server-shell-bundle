<?php

namespace ServerShellBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Repository\ScriptExecutionRepository;

class ScriptExecutionRepositoryTest extends TestCase
{
    public function testFindByNodeMethodExists(): void
    {
        $this->assertTrue(method_exists(ScriptExecutionRepository::class, 'findByNode'), '方法findByNode应该存在');
    }
    
    public function testFindByScriptMethodExists(): void
    {
        $this->assertTrue(method_exists(ScriptExecutionRepository::class, 'findByScript'), '方法findByScript应该存在');
    }
    
    public function testFindByStatusMethodExists(): void
    {
        $this->assertTrue(method_exists(ScriptExecutionRepository::class, 'findByStatus'), '方法findByStatus应该存在');
    }
    
    public function testFindByNodeAndScriptMethodExists(): void
    {
        $this->assertTrue(method_exists(ScriptExecutionRepository::class, 'findByNodeAndScript'), '方法findByNodeAndScript应该存在');
    }
} 