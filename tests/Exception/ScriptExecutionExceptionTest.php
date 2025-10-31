<?php

namespace ServerShellBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use ServerShellBundle\Exception\ScriptExecutionException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(ScriptExecutionException::class)]
final class ScriptExecutionExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return ScriptExecutionException::class;
    }

    protected function getExpectedParentClass(): string
    {
        return \RuntimeException::class;
    }
}
