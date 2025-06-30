<?php

namespace ServerShellBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use ServerShellBundle\Exception\ScriptUploadException;

class ScriptUploadExceptionTest extends TestCase
{
    public function testException(): void
    {
        $message = '脚本上传失败';
        $exception = new ScriptUploadException($message);
        
        $this->assertInstanceOf(ScriptUploadException::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}