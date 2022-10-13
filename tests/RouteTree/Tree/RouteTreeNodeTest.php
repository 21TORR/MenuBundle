<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\RouteTree\Tree;

use PHPUnit\Framework\TestCase;
use Torr\MenuBundle\RouteTree\Exception\InvalidRouteTreeException;
use Torr\MenuBundle\RouteTree\Tree\RouteTreeNode;

final class RouteTreeNodeTest extends TestCase
{
	/**
	 */
	public function testDuplicateParent () : void
	{
		$this->expectException(InvalidRouteTreeException::class);

		$node = new RouteTreeNode("route1");
		$parent = new RouteTreeNode("route2");
		$parent->addChild($node);

		$parent2 = new RouteTreeNode("route3");
		$parent2->addChild($node);
	}
}
