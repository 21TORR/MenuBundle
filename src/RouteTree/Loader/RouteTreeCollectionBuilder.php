<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Loader;

use Symfony\Component\Routing\Annotation\Route;
use Torr\MenuBundle\RouteTree\Collection\RouteTreeCollection;
use Torr\MenuBundle\RouteTree\Exception\InvalidRouteOptionsConfigurationException;
use Torr\MenuBundle\RouteTree\Tree\RouteTreeNode;

final class RouteTreeCollectionBuilder
{
	/**
	 * @param iterable<string, Route> $routes
	 * @internal
	 */
	public function build (iterable $routes) : RouteTreeCollection
	{
		$configuredRoutes = [];
		$parentsMap = [];
		$existingRouteKeys = [];

		// region Generate route tree nodes
		foreach ($routes as $routeKey => $route)
		{
			$existingRouteKeys[$routeKey] = true;
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
		$configuredRoutes = $this->mapParents($parentsMap, $configuredRoutes, $existingRouteKeys);
		// endregion

		return new RouteTreeCollection($configuredRoutes);
	}


	/**
	 * @param array<string, string>        $parentsMap
	 * @param array<string, RouteTreeNode> $configuredRoutes
	 * @param array<string, bool>          $existingRouteKeys
	 *
	 * @return array<string, RouteTreeNode>
	 */
	private function mapParents (
		array $parentsMap,
		array $configuredRoutes,
		array $existingRouteKeys,
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
				if (!\array_key_exists($parentRouteName, $existingRouteKeys))
				{
					throw new InvalidRouteOptionsConfigurationException(\sprintf(
						"Invalid route '%s':  The parent '%s' is not a known route.",
						$childRouteName,
						$parentRouteName,
					));
				}

				$parentNode = new RouteTreeNode($parentRouteName);
				$configuredRoutes[$parentRouteName] = $parentNode;
			}

			$parentNode->addChild($childNode);
		}

		return $configuredRoutes;
	}
}
