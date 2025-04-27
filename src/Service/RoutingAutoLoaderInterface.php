<?php

namespace Tourze\RoutingAutoLoaderBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;

#[AutoconfigureTag(self::TAG_NAME)]
interface RoutingAutoLoaderInterface
{
    const TAG_NAME = 'routing.auto.loader';

    public function autoload(): RouteCollection;
}
