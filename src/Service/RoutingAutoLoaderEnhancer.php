<?php

namespace Tourze\RoutingAutoLoaderBundle\Service;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Routing\RouteCollection;

#[AsDecorator(decorates: 'routing.loader')]
class RoutingAutoLoaderEnhancer implements LoaderInterface
{
    public function __construct(
        #[AutowireDecorated] private readonly LoaderInterface $inner,
        #[TaggedIterator(RoutingAutoLoaderInterface::TAG_NAME)] private readonly iterable $routingAutoLoaders,
    )
    {
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $collection = $this->inner->load($resource, $type);

        /** @var RoutingAutoLoaderInterface $autoloader */
        foreach ($this->routingAutoLoaders as $autoloader) {
            if ($autoloader instanceof RoutingAutoLoaderInterface) {
                $collection->addCollection($autoloader->autoload());
            }
        }

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $this->inner->supports($resource, $type);
    }

    public function getResolver(): LoaderResolverInterface
    {
        return $this->inner->getResolver();
    }

    public function setResolver(LoaderResolverInterface $resolver): void
    {
        $this->inner->setResolver($resolver);
    }
}
