<?php

namespace ServerShellBundle\Tests\Message;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Message\ScriptExecutionMessage;

class ScriptExecutionMessageTest extends TestCase
{
    /**
     * 测试消息构造和获取ID
     */
    public function testConstructAndGetId(): void
    {
        $executionId = 123;
        $message = new ScriptExecutionMessage($executionId);
        
        $this->assertEquals($executionId, $message->getExecutionId());
    }
    
    /**
     * 测试不同ID的消息比较
     */
    public function testCompareWithDifferentIds(): void
    {
        $message1 = new ScriptExecutionMessage(123);
        $message2 = new ScriptExecutionMessage(456);
        
        $this->assertNotEquals($message1->getExecutionId(), $message2->getExecutionId());
    }
} 