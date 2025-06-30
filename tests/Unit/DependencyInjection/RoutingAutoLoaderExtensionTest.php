<?php

namespace Tourze\RoutingAutoLoaderBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\RoutingAutoLoaderBundle\DependencyInjection\RoutingAutoLoaderExtension;

class RoutingAutoLoaderExtensionTest extends TestCase
{
    private RoutingAutoLoaderExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new RoutingAutoLoaderExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoadConfiguration(): void
    {
        $this->extension->load([], $this->container);

        // 验证服务资源已加载（使用资源配置的方式）
        $this->assertTrue($this->container->hasDefinition('Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderEnhancer'));
        
        // 验证服务配置正确
        $definition = $this->container->getDefinition('Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderEnhancer');
        $this->assertEquals('Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderEnhancer', $definition->getClass());
        $this->assertTrue($definition->isAutowired());
        $this->assertTrue($definition->isAutoconfigured());
    }

    public function testLoadWithEmptyConfig(): void
    {
        $configs = [];
        $this->extension->load($configs, $this->container);

        // 验证基本服务仍然被注册
        $this->assertTrue($this->container->hasDefinition('Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderEnhancer'));
    }

    public function testExtensionAlias(): void
    {
        $this->assertEquals('routing_auto_loader', $this->extension->getAlias());
    }
}