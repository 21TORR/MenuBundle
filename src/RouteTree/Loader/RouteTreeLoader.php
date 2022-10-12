<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Loader;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Torr\MenuBundle\Exception\MissingDependencyException;
use Torr\MenuBundle\RouteTree\Collection\RouteTreeCollection;
use Torr\MenuBundle\RouteTree\Exception\InvalidRouteOptionsConfigurationException;
use Torr\MenuBundle\RouteTree\Tree\RouteTreeNode;

final class RouteTreeLoader
{
	public function __construct (
		private readonly ?RouterInterface $router,
	) {}

	public function loadTree () : RouteTreeCollection
	{
		if (null === $this->router)
		{
			throw new MissingDependencyException("Can't use the route tree without a router.");
		}

		$configuredRoutes = [];
		$parentsMap = [];

		// region Generate route tree nodes
		foreach ($this->router->getRouteCollection() as $routeKey => $route)
		{
			$menuConfig = $route->getOption("menu");

			// skip unconfigured routes
			if (null === $menuConfig)
			{
				continue;
			}

			if (\is_string($menuConfig))
			{
				$menuConfig = ["parent" => $menuConfig];
			}

			if (!\is_array($menuConfig))
			{
				throw new InvalidRouteOptionsConfigurationException(\sprintf(
					"Invalid route '%s': The route options must either be a string or an array.",
					$routeKey,
				));
			}

			$parent = $menuConfig["parent"] ?? null;

			if (!\is_string($parent))
			{
				throw new InvalidRouteOptionsConfigurationException(\sprintf(
					"Invalid route '%s':  The 'parent' option is required and must be a string.",
					$routeKey,
				));
			}

			$configuredRoutes[$routeKey] = RouteTreeNode::fromArray($routeKey, $menuConfig);
			$parentsMap[$routeKey] = $parent;
		}
		// endregion

		// region Map Parents
		$configuredRoutes = $this->mapParents($parentsMap, $configuredRoutes);
		// endregion

		return new RouteTreeCollection($configuredRoutes);
	}


	/**
	 * @param array<string, string>        $parentsMap
	 * @param array<string, RouteTreeNode> $configuredRoutes
	 *
	 * @return array<string, RouteTreeNode>
	 */
	private function mapParents (
		array $parentsMap,
		array $configuredRoutes,
	) : array
	{
		foreach ($parentsMap as $childRouteName => $parentRouteName)
		{
			$childNode = $configuredRoutes[$childRouteName];
			\assert($childNode instanceof RouteTreeNode);
			$parentNode = $configuredRoutes[$parentRouteName] ?? null;

			// if parent does not exist, create it lazily
			if (null === $parentNode)
			{
				$parentNode = new RouteTreeNode($parentRouteName);
				$configuredRoutes[$parentRouteName] = $parentNode;
			}

			$parentNode->addChild($childNode);
		}

		return $configuredRoutes;
	}
}
