<?php

namespace ServerShellBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use ServerNodeBundle\Entity\Node;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Entity\ShellScript;
use ServerShellBundle\Enum\CommandStatus;

class ScriptExecutionTest extends TestCase
{
    private ShellScript $shellScript;
    private Node $node;
    
    protected function setUp(): void
    {
        // 创建模拟对象
        $this->shellScript = $this->createMock(ShellScript::class);
        $this->node = $this->createMock(Node::class);
        
        // 设置模拟对象的行为
        $this->shellScript->method('getName')->willReturn('测试脚本');
        $this->shellScript->method('__toString')->willReturn('测试脚本');
    }
    
    /**
     * 测试基本的getter和setter功能
     */
    public function testBasicGetterAndSetter(): void
    {
        $execution = new ScriptExecution();
        
        // 测试节点设置
        $execution->setNode($this->node);
        $this->assertSame($this->node, $execution->getNode());
        
        // 测试脚本设置
        $execution->setScript($this->shellScript);
        $this->assertSame($this->shellScript, $execution->getScript());
        
        // 测试状态设置
        $execution->setStatus(CommandStatus::RUNNING);
        $this->assertEquals(CommandStatus::RUNNING, $execution->getStatus());
        
        // 测试结果设置
        $execution->setResult('执行结果输出');
        $this->assertEquals('执行结果输出', $execution->getResult());
        
        // 测试执行时间设置
        $executedAt = new \DateTimeImmutable();
        $execution->setExecutedAt($executedAt);
        $this->assertEquals($executedAt, $execution->getExecutedAt());
        
        // 测试执行耗时设置
        $execution->setExecutionTime(2.5);
        $this->assertEquals(2.5, $execution->getExecutionTime());
        
        // 测试退出码设置
        $execution->setExitCode(0);
        $this->assertEquals(0, $execution->getExitCode());
    }
    
    
    /**
     * 测试时间设置
     */
    public function testTimeSettings(): void
    {
        $execution = new ScriptExecution();
        
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $execution->setCreateTime($createTime);
        $this->assertEquals($createTime, $execution->getCreateTime());
        
        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');
        $execution->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $execution->getUpdateTime());
    }
    
    /**
     * 测试状态枚举值
     */
    public function testStatusEnum(): void
    {
        $execution = new ScriptExecution();
        
        // 测试所有可能的状态值
        $statuses = [
            CommandStatus::PENDING,
            CommandStatus::RUNNING,
            CommandStatus::COMPLETED,
            CommandStatus::FAILED,
            CommandStatus::TIMEOUT,
            CommandStatus::CANCELED,
        ];
        
        foreach ($statuses as $status) {
            $execution->setStatus($status);
            $this->assertEquals($status, $execution->getStatus());
        }
    }
    
    /**
     * 测试toString方法
     */
    public function testToString(): void
    {
        $execution = new ScriptExecution();
        $execution->setScript($this->shellScript);
        
        $executedAt = new \DateTimeImmutable('2023-01-01 10:00:00');
        $execution->setExecutedAt($executedAt);
        
        $expected = '测试脚本 - 2023-01-01 10:00:00';
        $this->assertEquals($expected, (string)$execution);
    }
    
    /**
     * 测试null值处理
     */
    public function testNullValues(): void
    {
        $execution = new ScriptExecution();
        
        // 测试ID初始值
        $this->assertEquals(0, $execution->getId());
        
        // 测试结果为null
        $execution->setResult(null);
        $this->assertNull($execution->getResult());
        
        // 测试执行时间为null
        $execution->setExecutedAt(null);
        $this->assertNull($execution->getExecutedAt());
        
        // 测试执行耗时为null
        $execution->setExecutionTime(null);
        $this->assertNull($execution->getExecutionTime());
        
        // 测试退出码为null
        $execution->setExitCode(null);
        $this->assertNull($execution->getExitCode());
    }
} 