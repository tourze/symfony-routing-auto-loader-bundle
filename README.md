# Symfony Routing Auto Loader Bundle

[![Latest Stable Version](https://img.shields.io/packagist/v/tourze/symfony-routing-auto-loader-bundle.svg)](https://packagist.org/packages/tourze/symfony-routing-auto-loader-bundle)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A Symfony bundle for automatically loading custom route collections via tagged services. This bundle allows you to define and register your own route loaders, which will be automatically merged into the application's routing system.

## Features

- Auto-discover and load custom route collections via service tags
- Supports multiple route loaders
- Seamless integration with Symfony's routing component
- Simple interface for defining custom route providers

## Installation

### Requirements

- PHP 8.1+
- Symfony 6.4+

### Install via Composer

```bash
composer require tourze/symfony-routing-auto-loader-bundle
```

## Quick Start

1. **Define a class implementing `RoutingAutoLoaderInterface`**

```php
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Symfony\Component\Routing\RouteCollection;

class MyRouteLoader implements RoutingAutoLoaderInterface
{
    public function autoload(): RouteCollection
    {
        $routes = new RouteCollection();
        // add routes ...
        return $routes;
    }
}
```

2. **Register your loader as a service**

```yaml
# config/services.yaml
services:
    App\Routing\MyRouteLoader:
        tags: ['routing.auto.loader']
```

3. **Routes will be auto-included in your app**

## Documentation

- Implement the `RoutingAutoLoaderInterface` and tag your service with `routing.auto.loader`.
- All tagged services will be called and their routes merged into the main collection.
- Advanced: You can create multiple loaders for different modules or route groups.

## Contribution

Contributions are welcome! Please submit issues or pull requests. Ensure code style and tests pass before submitting.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Changelog

See the [releases page](https://packagist.org/packages/tourze/symfony-routing-auto-loader-bundle#releases) for changelog and upgrade notes.
