<?php

namespace ServerShellBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Repository\ShellScriptRepository;

class ShellScriptRepositoryTest extends TestCase
{
    public function testFindByTagsMethodExists(): void
    {
        $this->assertTrue(method_exists(ShellScriptRepository::class, 'findByTags'), '方法findByTags应该存在');
    }
    
    public function testFindAllEnabledMethodExists(): void
    {
        $this->assertTrue(method_exists(ShellScriptRepository::class, 'findAllEnabled'), '方法findAllEnabled应该存在');
    }
} 