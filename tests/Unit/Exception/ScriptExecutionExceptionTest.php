<?php

namespace ServerShellBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Exception\ScriptExecutionException;

class ScriptExecutionExceptionTest extends TestCase
{
    public function testException(): void
    {
        $message = '脚本执行失败';
        $exception = new ScriptExecutionException($message);
        
        $this->assertInstanceOf(ScriptExecutionException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}