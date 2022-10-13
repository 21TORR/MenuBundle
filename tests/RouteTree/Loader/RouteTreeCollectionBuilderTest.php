<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\RouteTree\Loader;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Torr\MenuBundle\RouteTree\Exception\InvalidRouteOptionsConfigurationException;
use Torr\MenuBundle\RouteTree\Loader\RouteTreeCollectionBuilder;

final class RouteTreeCollectionBuilderTest extends TestCase
{
	/**
	 */
	public function testLoading () : void
	{
		$routes = [
			"parent" => new Route("/parent"),
			"route.a" => new Route("/a", options: ["menu" => "parent"]),
			"route.b" => new Route("/b", options: ["menu" => [
				"parent" => "parent",
				"label" => "test",
			]]),
			"unused" => new Route("/unused"),
		];

		$builder = new RouteTreeCollectionBuilder();
		$collection = $builder->build($routes);

		$parent = $collection->getNodeByRoute("parent");
		$routeA = $collection->getNodeByRoute("route.a");
		$routeB = $collection->getNodeByRoute("route.b");
		self::assertSame($parent, $routeA->getParent());
		self::assertSame($parent, $routeB->getParent());
		self::assertNull($parent->getParent());
		self::assertNull($collection->getNodeByRoute("unused"));
		self::assertSame("test", $routeB->getLabel());
	}

	/**
	 */
	public function testInvalidKey () : void
	{
		$this->expectException(InvalidRouteOptionsConfigurationException::class);
		$this->expectExceptionMessage("Invalid route 'route': The route options must either be a string or an array.");

		$builder = new RouteTreeCollectionBuilder();
		$builder->build([
			"route" => new Route("/path", options: ["menu" => 123]),
		]);
	}

	/**
	 */
	public function testInvalidParent () : void
	{
		$this->expectException(InvalidRouteOptionsConfigurationException::class);
		$this->expectExceptionMessage("Invalid route 'route':  The 'parent' option is required and must be a string.");

		$builder = new RouteTreeCollectionBuilder();
		$builder->build([
			"route" => new Route("/path", options: ["menu" => ["parent" => 123]]),
		]);
	}

	/**
	 */
	public function testMissingParent () : void
	{
		$this->expectException(InvalidRouteOptionsConfigurationException::class);
		$this->expectExceptionMessage("Invalid route 'route':  The parent 'missing-parent' is not a known route.");

		$builder = new RouteTreeCollectionBuilder();
		$builder->build([
			"route" => new Route("/path", options: ["menu" => "missing-parent"]),
		]);
	}
}
