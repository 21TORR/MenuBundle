<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\RouteTree\Loader;

use PHPUnit\Framework\TestCase;
use Torr\MenuBundle\Exception\MissingDependencyException;
use Torr\MenuBundle\RouteTree\Loader\RouteTreeCollectionBuilder;
use Torr\MenuBundle\RouteTree\Loader\RouteTreeLoader;

final class RouteTreeLoaderTest extends TestCase
{
	/**
	 */
	public function testLoadingWithoutRouter () : void
	{
		$this->expectException(MissingDependencyException::class);

		$loader = new RouteTreeLoader(new RouteTreeCollectionBuilder(), null);
		$loader->loadTree();
	}
}
