<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Render;

use Torr\MenuBundle\Render\MenuRenderer;
use Torr\MenuBundle\Render\Options\RenderOptions;
use Torr\MenuBundle\RouteTree\Exception\UnknownRouteNameException;
use Torr\MenuBundle\RouteTree\Loader\RouteTreeLoader;
use Torr\MenuBundle\RouteTree\Options\RouteTreeOptions;
use Torr\MenuBundle\RouteTree\Transformer\RouteTreeTransformHelper;

/**
 * @final
 */
class RouteMenuRenderer
{
	/**
	 */
	public function __construct (
		private readonly RouteTreeLoader $loader,
		private readonly RouteTreeTransformHelper $transformHelper,
		private readonly MenuRenderer $menuRenderer,
	) {}

	/**
	 *
	 */
	public function render (
		string $routeName,
		RouteTreeOptions $routeTreeOptions = new RouteTreeOptions(),
		RenderOptions $renderOptions = new RenderOptions(),
	) : string
	{
		$tree = $this->loader->loadTree();
		$node = $tree->getNodeByRoute($routeName);

		if (null === $node)
		{
			throw new UnknownRouteNameException(\sprintf(
				"Route '%s' does not exist or is not used in a tree.",
				$routeName,
			));
		}

		$menu = $node->toMenuItem($this->transformHelper, $routeTreeOptions);
		return $this->menuRenderer->render($menu, $renderOptions);
	}
}
