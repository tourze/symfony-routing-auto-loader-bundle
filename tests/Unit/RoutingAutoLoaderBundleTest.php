<?php

namespace Tourze\RoutingAutoLoaderBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

class RoutingAutoLoaderBundleTest extends TestCase
{
    private RoutingAutoLoaderBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new RoutingAutoLoaderBundle();
    }

    public function testBundleExtendsSymfonyBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testBundleImplementsBundleDependencyInterface(): void
    {
        $this->assertInstanceOf(BundleDependencyInterface::class, $this->bundle);
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = $this->bundle->getBundleDependencies();

        $this->assertArrayHasKey(FrameworkBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[FrameworkBundle::class]);
    }

    public function testBundleName(): void
    {
        $this->assertEquals('RoutingAutoLoaderBundle', $this->bundle->getName());
    }

    public function testBundleNamespace(): void
    {
        $this->assertEquals('Tourze\RoutingAutoLoaderBundle', $this->bundle->getNamespace());
    }
}