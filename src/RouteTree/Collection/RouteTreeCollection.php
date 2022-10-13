<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Collection;

use Torr\MenuBundle\RouteTree\Tree\RouteTreeNode;

final class RouteTreeCollection
{
	/**
	 *
	 */
	private readonly array $nodes;

	/**
	 * @param iterable<RouteTreeNode> $nodes
	 */
	public function __construct (
		iterable $nodes,
	)
	{
		$indexed = [];

		foreach ($nodes as $node)
		{
			$indexed[$node->getRoute()] = $node;
		}

		$this->nodes = $indexed;
	}

	/**
	 */
	public function getNodeByRoute (string $route) : ?RouteTreeNode
	{
		return $this->nodes[$route] ?? null;
	}
}
