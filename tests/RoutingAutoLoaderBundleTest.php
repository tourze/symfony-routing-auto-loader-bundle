<?php

declare(strict_types=1);

namespace Tourze\RoutingAutoLoaderBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

/**
 * @internal
 */
#[CoversClass(RoutingAutoLoaderBundle::class)]
#[RunTestsInSeparateProcesses]
final class RoutingAutoLoaderBundleTest extends AbstractBundleTestCase
{
}
