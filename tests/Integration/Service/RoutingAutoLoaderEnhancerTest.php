<?php

namespace Tourze\RoutingAutoLoaderBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderEnhancer;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

class RoutingAutoLoaderEnhancerTest extends TestCase
{
    private LoaderInterface $innerLoader;
    private RoutingAutoLoaderInterface $autoLoader;

    protected function setUp(): void
    {
        // 创建内部加载器模拟
        $this->innerLoader = $this->createMock(LoaderInterface::class);
        
        // 创建自动加载器模拟
        $this->autoLoader = $this->createMock(RoutingAutoLoaderInterface::class);
    }

    public function testIntegrationBehavior(): void
    {
        // 设置内部加载器返回基础路由集合
        $originalCollection = new RouteCollection();
        $originalCollection->add('base_route', new Route('/base'));
        
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($originalCollection);

        // 设置自动加载器返回自动路由集合
        $autoCollection = new RouteCollection();
        $autoCollection->add('auto_route', new Route('/auto'));
        
        $this->autoLoader->expects($this->once())
            ->method('autoload')
            ->willReturn($autoCollection);

        // 创建服务增强器
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, [$this->autoLoader]);

        // 执行集成测试
        $result = $enhancer->load('test_resource');

        // 验证集成结果
        $this->assertInstanceOf(RouteCollection::class, $result);
        $this->assertCount(2, $result);
        $this->assertNotNull($result->get('base_route'));
        $this->assertNotNull($result->get('auto_route'));
    }

    public function testServiceIntegrationWithTaggedIterator(): void
    {
        // 创建多个自动加载器
        $autoLoader1 = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoLoader2 = $this->createMock(RoutingAutoLoaderInterface::class);

        // 设置返回集合
        $baseCollection = new RouteCollection();
        $baseCollection->add('base', new Route('/base'));

        $autoCollection1 = new RouteCollection();
        $autoCollection1->add('auto1', new Route('/auto1'));

        $autoCollection2 = new RouteCollection();
        $autoCollection2->add('auto2', new Route('/auto2'));

        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($baseCollection);

        $autoLoader1->expects($this->once())
            ->method('autoload')
            ->willReturn($autoCollection1);

        $autoLoader2->expects($this->once())
            ->method('autoload')
            ->willReturn($autoCollection2);

        // 模拟tagged iterator行为
        $taggedIterator = [$autoLoader1, $autoLoader2];
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, $taggedIterator);

        $result = $enhancer->load('test_resource');

        $this->assertCount(3, $result);
        $this->assertNotNull($result->get('base'));
        $this->assertNotNull($result->get('auto1'));
        $this->assertNotNull($result->get('auto2'));
    }
}