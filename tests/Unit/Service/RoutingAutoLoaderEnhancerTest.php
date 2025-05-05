<?php

namespace Tourze\RoutingAutoLoaderBundle\Tests\Unit\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderEnhancer;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

class RoutingAutoLoaderEnhancerTest extends TestCase
{
    private LoaderInterface|MockObject $innerLoader;
    private RouteCollection $originalCollection;
    private LoaderResolverInterface|MockObject $resolver;

    protected function setUp(): void
    {
        // 创建内部加载器模拟对象
        $this->innerLoader = $this->createMock(LoaderInterface::class);
        $this->resolver = $this->createMock(LoaderResolverInterface::class);

        // 创建一个基础路由集合
        $this->originalCollection = new RouteCollection();
        $this->originalCollection->add('original_route', new Route('/original'));
    }

    /**
     * 测试无自动加载器时的加载行为
     */
    public function testLoad_withNoAutoLoaders_shouldReturnOriginalCollection(): void
    {
        // 设置内部加载器返回原始集合
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($this->originalCollection);

        // 创建增强器实例，无自动加载器
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, []);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertSame($this->originalCollection, $result);
        $this->assertCount(1, $result);
        $this->assertTrue($result->get('original_route') !== null);
    }

    /**
     * 测试单个自动加载器的加载行为
     */
    public function testLoad_withSingleAutoLoader_shouldMergeRoutes(): void
    {
        // 设置内部加载器返回原始集合
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($this->originalCollection);

        // 创建自动加载器模拟对象
        $autoLoader = $this->createMock(RoutingAutoLoaderInterface::class);

        // 创建自动加载路由集合
        $autoRoutes = new RouteCollection();
        $autoRoutes->add('auto_route', new Route('/auto'));

        // 设置自动加载器返回路由集合
        $autoLoader->expects($this->once())
            ->method('autoload')
            ->willReturn($autoRoutes);

        // 创建增强器实例，带单个自动加载器
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, [$autoLoader]);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertCount(2, $result);
        $this->assertTrue($result->get('original_route') !== null);
        $this->assertTrue($result->get('auto_route') !== null);
    }

    /**
     * 测试多个自动加载器的加载行为
     */
    public function testLoad_withMultipleAutoLoaders_shouldMergeAllRoutes(): void
    {
        // 设置内部加载器返回原始集合
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($this->originalCollection);

        // 创建第一个自动加载器
        $autoLoader1 = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoRoutes1 = new RouteCollection();
        $autoRoutes1->add('auto_route1', new Route('/auto1'));
        $autoLoader1->expects($this->once())
            ->method('autoload')
            ->willReturn($autoRoutes1);

        // 创建第二个自动加载器
        $autoLoader2 = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoRoutes2 = new RouteCollection();
        $autoRoutes2->add('auto_route2', new Route('/auto2'));
        $autoLoader2->expects($this->once())
            ->method('autoload')
            ->willReturn($autoRoutes2);

        // 创建增强器实例，带多个自动加载器
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, [$autoLoader1, $autoLoader2]);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertCount(3, $result);
        $this->assertTrue($result->get('original_route') !== null);
        $this->assertTrue($result->get('auto_route1') !== null);
        $this->assertTrue($result->get('auto_route2') !== null);
    }

    /**
     * 测试自动加载器返回空集合的行为
     */
    public function testLoad_withEmptyAutoLoaderCollection_shouldNotChangeOriginal(): void
    {
        // 设置内部加载器返回原始集合
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($this->originalCollection);

        // 创建返回空集合的自动加载器
        $autoLoader = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoLoader->expects($this->once())
            ->method('autoload')
            ->willReturn(new RouteCollection());

        // 创建增强器实例
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, [$autoLoader]);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertCount(1, $result);
        $this->assertTrue($result->get('original_route') !== null);
    }

    /**
     * 测试路由名称冲突的处理
     */
    public function testLoad_withConflictingRouteNames_shouldOverrideExistingRoutes(): void
    {
        // 设置内部加载器返回原始集合
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($this->originalCollection);

        // 创建自动加载器，返回的路由与原始路由名称冲突
        $autoLoader = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoRoutes = new RouteCollection();
        $conflictingRoute = new Route('/override');
        $autoRoutes->add('original_route', $conflictingRoute);

        $autoLoader->expects($this->once())
            ->method('autoload')
            ->willReturn($autoRoutes);

        // 创建增强器实例
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, [$autoLoader]);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertCount(1, $result);
        $this->assertSame($conflictingRoute, $result->get('original_route'));
    }

    /**
     * 测试supports方法是否正确代理到内部加载器
     */
    public function testSupports_shouldDelegateToInnerLoader(): void
    {
        // 设置内部加载器的supports方法返回值
        $this->innerLoader->expects($this->once())
            ->method('supports')
            ->with('resource', 'type')
            ->willReturn(true);

        // 创建增强器实例
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, []);

        // 验证supports方法是否正确代理
        $this->assertTrue($enhancer->supports('resource', 'type'));
    }

    /**
     * 测试getResolver方法是否正确代理到内部加载器
     */
    public function testGetResolver_shouldDelegateToInnerLoader(): void
    {
        // 设置内部加载器的getResolver方法返回值
        $this->innerLoader->expects($this->once())
            ->method('getResolver')
            ->willReturn($this->resolver);

        // 创建增强器实例
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, []);

        // 验证getResolver方法是否正确代理
        $this->assertSame($this->resolver, $enhancer->getResolver());
    }

    /**
     * 测试setResolver方法是否正确代理到内部加载器
     */
    public function testSetResolver_shouldDelegateToInnerLoader(): void
    {
        // 期望内部加载器的setResolver方法被调用一次
        $this->innerLoader->expects($this->once())
            ->method('setResolver')
            ->with($this->resolver);

        // 创建增强器实例
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, []);

        // 调用setResolver方法
        $enhancer->setResolver($this->resolver);
    }

    /**
     * 测试自动加载器抛出异常时的处理
     */
    public function testLoad_whenAutoLoaderThrowsException_shouldPropagateException(): void
    {
        // 设置内部加载器返回原始集合
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($this->originalCollection);

        // 创建抛出异常的自动加载器
        $autoLoader = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoLoader->expects($this->once())
            ->method('autoload')
            ->willThrowException(new \RuntimeException('Auto loader error'));

        // 创建增强器实例
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, [$autoLoader]);

        // 期望异常被传播
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Auto loader error');

        // 执行加载
        $enhancer->load('resource');
    }

    /**
     * 测试非RoutingAutoLoaderInterface类型的条目被忽略
     */
    public function testLoad_withNonInterfaceObjects_shouldIgnoreThem(): void
    {
        // 设置内部加载器返回原始集合
        $this->innerLoader->expects($this->once())
            ->method('load')
            ->willReturn($this->originalCollection);

        // 创建自动加载器
        $autoLoader = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoRoutes = new RouteCollection();
        $autoRoutes->add('auto_route', new Route('/auto'));
        $autoLoader->expects($this->once())
            ->method('autoload')
            ->willReturn($autoRoutes);

        // 创建非接口对象
        $nonInterface = new \stdClass();

        // 创建增强器实例，同时包含接口实现和非接口对象
        $enhancer = new RoutingAutoLoaderEnhancer($this->innerLoader, [$autoLoader, $nonInterface]);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertCount(2, $result);
        $this->assertTrue($result->get('original_route') !== null);
        $this->assertTrue($result->get('auto_route') !== null);
    }
}
