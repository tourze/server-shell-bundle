<?php

namespace ServerShellBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use ServerShellBundle\Exception\ScriptDisabledException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(ScriptDisabledException::class)]
final class ScriptDisabledExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return ScriptDisabledException::class;
    }

    protected function getExpectedParentClass(): string
    {
        return \RuntimeException::class;
    }
}
