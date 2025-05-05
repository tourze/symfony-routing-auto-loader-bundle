<?php

namespace Tourze\RoutingAutoLoaderBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderEnhancer;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;

/**
 * 简化的集成测试，不依赖于完整的Symfony内核
 */
class SimplifiedIntegrationTest extends TestCase
{
    /**
     * 测试路由自动加载器是否正确合并路由集合
     */
    public function testRouteAutoLoaderCorrectlyMergesCollections(): void
    {
        // 创建原始路由集合
        $originalCollection = new RouteCollection();
        $originalCollection->add('original_route', new Route('/original'));

        // 创建模拟内部加载器
        $innerLoader = $this->createMock(\Symfony\Component\Config\Loader\LoaderInterface::class);
        $innerLoader->method('load')->willReturn($originalCollection);
        $innerLoader->method('supports')->willReturn(true);

        // 创建自动加载器
        $autoLoader1 = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoCollection1 = new RouteCollection();
        $autoCollection1->add('auto_route_1', new Route('/auto-1'));
        $autoLoader1->method('autoload')->willReturn($autoCollection1);

        $autoLoader2 = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoCollection2 = new RouteCollection();
        $autoCollection2->add('auto_route_2', new Route('/auto-2'));
        $autoLoader2->method('autoload')->willReturn($autoCollection2);

        // 创建增强器
        $enhancer = new RoutingAutoLoaderEnhancer($innerLoader, [$autoLoader1, $autoLoader2]);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertCount(3, $result->all());
        $this->assertNotNull($result->get('original_route'));
        $this->assertNotNull($result->get('auto_route_1'));
        $this->assertNotNull($result->get('auto_route_2'));
    }

    /**
     * 测试路由名称冲突的处理
     */
    public function testRouteNameConflictHandling(): void
    {
        // 创建原始路由集合
        $originalCollection = new RouteCollection();
        $originalCollection->add('conflicting_route', new Route('/original-path'));

        // 创建模拟内部加载器
        $innerLoader = $this->createMock(\Symfony\Component\Config\Loader\LoaderInterface::class);
        $innerLoader->method('load')->willReturn($originalCollection);

        // 创建自动加载器，其路由名称与原始路由冲突
        $autoLoader = $this->createMock(RoutingAutoLoaderInterface::class);
        $autoCollection = new RouteCollection();
        $autoCollection->add('conflicting_route', new Route('/auto-path'));
        $autoLoader->method('autoload')->willReturn($autoCollection);

        // 创建增强器
        $enhancer = new RoutingAutoLoaderEnhancer($innerLoader, [$autoLoader]);

        // 执行加载
        $result = $enhancer->load('resource');

        // 验证结果
        $this->assertCount(1, $result->all());
        $this->assertNotNull($result->get('conflicting_route'));
        // 验证最后添加的路由覆盖了原始路由
        $this->assertEquals('/auto-path', $result->get('conflicting_route')->getPath());
    }

    /**
     * 测试接口自动配置标签功能
     */
    public function testInterfaceAutoconfigureTagFeature(): void
    {
        // 检查接口上是否有正确的属性
        $reflectionClass = new \ReflectionClass(RoutingAutoLoaderInterface::class);

        $hasAutoconfigureTag = false;
        foreach ($reflectionClass->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();
            if ($attributeName === 'Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag') {
                $hasAutoconfigureTag = true;
                break;
            }
        }

        $this->assertTrue($hasAutoconfigureTag, 'RoutingAutoLoaderInterface应该有AutoconfigureTag属性');
    }
}
