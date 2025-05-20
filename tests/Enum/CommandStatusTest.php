<?php

namespace ServerShellBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Enum\CommandStatus;

class CommandStatusTest extends TestCase
{
    /**
     * 测试枚举值常量
     */
    public function testEnumValues(): void
    {
        $this->assertEquals('pending', CommandStatus::PENDING->value);
        $this->assertEquals('running', CommandStatus::RUNNING->value);
        $this->assertEquals('completed', CommandStatus::COMPLETED->value);
        $this->assertEquals('failed', CommandStatus::FAILED->value);
        $this->assertEquals('timeout', CommandStatus::TIMEOUT->value);
        $this->assertEquals('canceled', CommandStatus::CANCELED->value);
    }
    
    /**
     * 测试从字符串创建枚举实例
     */
    public function testFromString(): void
    {
        $this->assertEquals(CommandStatus::PENDING, CommandStatus::from('pending'));
        $this->assertEquals(CommandStatus::RUNNING, CommandStatus::from('running'));
        $this->assertEquals(CommandStatus::COMPLETED, CommandStatus::from('completed'));
        $this->assertEquals(CommandStatus::FAILED, CommandStatus::from('failed'));
        $this->assertEquals(CommandStatus::TIMEOUT, CommandStatus::from('timeout'));
        $this->assertEquals(CommandStatus::CANCELED, CommandStatus::from('canceled'));
    }
    
    /**
     * 测试使用无效字符串创建枚举将抛出异常
     */
    public function testInvalidFromString(): void
    {
        $this->expectException(\ValueError::class);
        CommandStatus::from('invalid_status');
    }
    
    /**
     * 测试枚举实例比较
     */
    public function testCompareEnums(): void
    {
        $this->assertTrue(CommandStatus::PENDING === CommandStatus::PENDING);
        $this->assertFalse(CommandStatus::PENDING === CommandStatus::RUNNING);
        $this->assertTrue(CommandStatus::PENDING !== CommandStatus::RUNNING);
    }
    
    /**
     * 测试枚举实例可以用于switch语句
     */
    public function testEnumInSwitch(): void
    {
        $status = CommandStatus::RUNNING;
        
        $result = '';
        switch ($status) {
            case CommandStatus::PENDING:
                $result = 'pending';
                break;
            case CommandStatus::RUNNING:
                $result = 'running';
                break;
            default:
                $result = 'other';
        }
        
        $this->assertEquals('running', $result);
    }
} 