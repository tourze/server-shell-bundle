<?php

namespace ServerShellBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use ServerShellBundle\Exception\ScriptUploadException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(ScriptUploadException::class)]
final class ScriptUploadExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return ScriptUploadException::class;
    }

    protected function getExpectedParentClass(): string
    {
        return \RuntimeException::class;
    }
}
