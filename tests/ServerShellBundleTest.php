<?php

declare(strict_types=1);

namespace ServerShellBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ServerShellBundle\ServerShellBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(ServerShellBundle::class)]
#[RunTestsInSeparateProcesses]
final class ServerShellBundleTest extends AbstractBundleTestCase
{
}
