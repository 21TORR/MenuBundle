<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Tree;

use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\RouteTree\Exception\InvalidRouteTreeException;
use Torr\MenuBundle\RouteTree\Options\RouteTreeOptions;
use Torr\MenuBundle\RouteTree\Transformer\RouteTreeTransformHelper;
use Torr\Rad\Route\Linkable;

final class RouteTreeNode
{
	private ?self $parent = null;
	/** @var self[] */
	private array $children = [];

	/**
	 */
	public function __construct (
		private readonly string $route,
		private readonly array $routeParameters = [],
		private readonly ?string $label = null,
		private readonly bool $sort = false,
		private readonly ?string $security = null,
		private readonly int $priority = 0,
		private readonly bool $hidden = false,
	) {}

	/**
	 */
	public function getRoute () : string
	{
		return $this->route;
	}


	/**
	 *
	 */
	public function addChild (self $child) : void
	{
		if (null !== $child->parent)
		{
			throw new InvalidRouteTreeException(\sprintf(
				"Can't add child '%s' to parent '%s' as it already has a parent ('%s')",
				$child->route,
				$this->route,
				$child->parent->route,
			));
		}

		$child->parent = $this;
		$this->children[] = $child;
	}


	/**
	 * Transforms the route tree to a menu item tree
	 */
	public function toMenuItem (
		RouteTreeTransformHelper $transformHelper,
		RouteTreeOptions $options,
	) : MenuItem
	{
		$menuItem = new MenuItem(
			label: $transformHelper->translate($this->label, $options->getTranslationDomain()),
			target: new Linkable($this->route, $this->routeParameters),
			extras: [
				"priority" => $this->priority,
			],
			visible: !$this->hidden && $transformHelper->isAccessible($this->security),
		);

		// handle children
		$children = [];

		foreach ($this->children as $child)
		{
			$children[] = $child->toMenuItem($transformHelper, $options);
		}

		// handle sorting
		if ($this->sort)
		{
			$children = $transformHelper->sortMenuItems($children);
		}


		// add to parent
		\array_map(
			$menuItem->addChild(...),
			$children,
		);

		return $menuItem;
	}


	public static function fromArray (string $route, array $config) : self
	{
		$config["route"] = $route;
		unset($config["parent"]);

		return new self(...$config);
	}
}
