<?php

namespace ServerShellBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Entity\ShellScript;

class ShellScriptTest extends TestCase
{
    /**
     * 测试基本的getter和setter功能
     */
    public function testBasicGetterAndSetter(): void
    {
        $shellScript = new ShellScript();
        
        // 测试名称
        $shellScript->setName('测试脚本');
        $this->assertEquals('测试脚本', $shellScript->getName());
        
        // 测试内容
        $shellScript->setContent('echo "Hello World"');
        $this->assertEquals('echo "Hello World"', $shellScript->getContent());
        
        // 测试工作目录
        $shellScript->setWorkingDirectory('/tmp');
        $this->assertEquals('/tmp', $shellScript->getWorkingDirectory());
        
        // 测试是否使用sudo
        $shellScript->setUseSudo(true);
        $this->assertTrue($shellScript->isUseSudo());
        
        // 测试超时时间
        $shellScript->setTimeout(600);
        $this->assertEquals(600, $shellScript->getTimeout());
        
        // 测试启用状态
        $shellScript->setEnabled(false);
        $this->assertFalse($shellScript->isEnabled());
        
        // 测试标签
        $tags = ['deployment', 'backup'];
        $shellScript->setTags($tags);
        $this->assertEquals($tags, $shellScript->getTags());
        
        // 测试描述
        $shellScript->setDescription('这是一个测试脚本');
        $this->assertEquals('这是一个测试脚本', $shellScript->getDescription());
    }
    
    
    /**
     * 测试时间设置
     */
    public function testTimeSettings(): void
    {
        $shellScript = new ShellScript();
        
        $createTime = new \DateTimeImmutable('2023-01-01 10:00:00');
        $shellScript->setCreateTime($createTime);
        $this->assertEquals($createTime, $shellScript->getCreateTime());
        
        $updateTime = new \DateTimeImmutable('2023-01-02 11:00:00');
        $shellScript->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $shellScript->getUpdateTime());
    }
    
    /**
     * 测试toString方法
     */
    public function testToString(): void
    {
        $shellScript = new ShellScript();
        $shellScript->setName('测试脚本');
        
        $this->assertEquals('测试脚本', (string)$shellScript);
    }
    
    /**
     * 测试边界情况
     */
    public function testEdgeCases(): void
    {
        $shellScript = new ShellScript();
        
        // 测试ID初始值
        $this->assertEquals(0, $shellScript->getId());
        
        // 测试空值处理
        $shellScript->setDescription(null);
        $this->assertNull($shellScript->getDescription());
        
        $shellScript->setWorkingDirectory(null);
        $this->assertNull($shellScript->getWorkingDirectory());
        
        $shellScript->setTags(null);
        $this->assertNull($shellScript->getTags());
    }
} 