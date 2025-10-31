<?php

namespace ServerShellBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use ServerShellBundle\Exception\AdminMenuException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenuException::class)]
final class AdminMenuExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return AdminMenuException::class;
    }

    protected function getExpectedParentClass(): string
    {
        return \RuntimeException::class;
    }
}
