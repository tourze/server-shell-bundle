<?php

namespace ServerShellBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Exception\ScriptDisabledException;

class ScriptDisabledExceptionTest extends TestCase
{
    public function testException(): void
    {
        $message = '脚本已禁用';
        $exception = new ScriptDisabledException($message);
        
        $this->assertInstanceOf(ScriptDisabledException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}