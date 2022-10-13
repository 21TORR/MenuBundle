<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\RouteTree\Loader;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Routing\RouterInterface;
use Torr\MenuBundle\Exception\MissingOptionalDependencyException;
use Torr\MenuBundle\RouteTree\Collection\RouteTreeCollection;
use Torr\MenuBundle\RouteTree\Loader\RouteTreeCollectionBuilder;
use Torr\MenuBundle\RouteTree\Loader\RouteTreeLoader;

final class RouteTreeLoaderTest extends TestCase
{
	/**
	 */
	public function testLoadingWithoutRouter () : void
	{
		$this->expectException(MissingOptionalDependencyException::class);

		$loader = new RouteTreeLoader(
			new RouteTreeCollectionBuilder(),
			new ConfigCacheFactory(false),
			"/cache",
			null,
		);
		$loader->loadTree();
	}


	public function testIntegration () : void
	{
		$configCacheFactory = $this->createMock(ConfigCacheFactoryInterface::class);
		$configCache = $this->createMock(ConfigCacheInterface::class);
		$router = $this->createMock(RouterInterface::class);

		$configCache
			->expects(self::once())
			->method("getPath")
			->willReturn(__DIR__ . "/../../fixtures/example-cache-file.php");

		$configCacheFactory
			->expects(self::once())
			->method("cache")
			->with("/path/to/cache/21torr/menu-bundle/route-tree-collection.php")
			->willReturn($configCache);

		$loader = new RouteTreeLoader(
			new RouteTreeCollectionBuilder(),
			$configCacheFactory,
			"/path/to/cache",
			$router,
		);

		$loader->loadTree();
	}
}
