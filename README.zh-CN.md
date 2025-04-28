# Symfony Routing Auto Loader Bundle

[![最新稳定版本](https://img.shields.io/packagist/v/tourze/symfony-routing-auto-loader-bundle.svg)](https://packagist.org/packages/tourze/symfony-routing-auto-loader-bundle)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

一个用于自动加载自定义路由集合的 Symfony Bundle。通过服务标签自动发现并合并自定义路由加载器，简化路由模块化开发。

## 功能特性

- 通过服务标签自动发现和加载自定义路由集合
- 支持多个路由加载器并自动合并
- 与 Symfony 路由组件无缝集成
- 提供简单接口定义自定义路由提供者

## 安装说明

### 环境要求

- PHP 8.1 及以上
- Symfony 6.4 及以上

### Composer 安装

```bash
composer require tourze/symfony-routing-auto-loader-bundle
```

## 快速开始

1. **实现 `RoutingAutoLoaderInterface` 接口**

```php
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Symfony\Component\Routing\RouteCollection;

class MyRouteLoader implements RoutingAutoLoaderInterface
{
    public function autoload(): RouteCollection
    {
        $routes = new RouteCollection();
        // 添加路由 ...
        return $routes;
    }
}
```

2. **将加载器注册为服务并打标签**

```yaml
# config/services.yaml
services:
    App\Routing\MyRouteLoader:
        tags: ['routing.auto.loader']
```

3. **所有自定义路由会自动合并到主路由集合**

## 文档说明

- 实现 `RoutingAutoLoaderInterface` 并为服务添加 `routing.auto.loader` 标签
- 所有被打标签的服务会自动调用 `autoload` 并合并其路由集合
- 可为不同模块或业务分组编写多个加载器

## 贡献指南

欢迎提交 Issue 或 PR。请确保代码风格和测试通过。

## 协议

MIT 协议，详见 [LICENSE](LICENSE)。

## 更新日志

详见 [releases 页面](https://packagist.org/packages/tourze/symfony-routing-auto-loader-bundle#releases) 以获取变更记录和升级说明。
