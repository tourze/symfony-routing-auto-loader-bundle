<?php

namespace Tourze\RoutingAutoLoaderBundle;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class RoutingAutoLoaderBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            FrameworkBundle::class => ['all' => true],
        ];
    }
}
