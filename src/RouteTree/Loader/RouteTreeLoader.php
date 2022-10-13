<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Loader;

use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Routing\RouterInterface;
use Torr\MenuBundle\Exception\MissingDependencyException;
use Torr\MenuBundle\RouteTree\Collection\RouteTreeCollection;

final class RouteTreeLoader
{
	/**
	 */
	public function __construct (
		private readonly RouteTreeCollectionBuilder $builder,
		private readonly ConfigCacheFactoryInterface $configCacheFactory,
		private readonly string $cacheDir,
		private readonly ?RouterInterface $router = null,
	) {}

	/**
	 *
	 */
	public function loadTree () : RouteTreeCollection
	{
		if (null === $this->router)
		{
			throw new MissingDependencyException("Can't use the route tree without a router.");
		}

		$cache = $this->configCacheFactory->cache(
			"{$this->cacheDir}/21torr/menu-bundle/route-tree-collection.php",
			function (ConfigCacheInterface $cache) : void
			{
				$routeCollection = $this->router->getRouteCollection();
				$routeTreeCollection = $this->builder->build($routeCollection);

				$cache->write(
					\sprintf(
						'<?php return \\unserialize(%s);',
						\var_export(\serialize($routeTreeCollection), true),
					),
					$routeCollection->getResources(),
				);
			},
		);

		return include $cache->getPath();
	}
}
