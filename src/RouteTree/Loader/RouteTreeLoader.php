<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Loader;

use Symfony\Component\Routing\RouterInterface;
use Torr\MenuBundle\Exception\MissingDependencyException;
use Torr\MenuBundle\RouteTree\Collection\RouteTreeCollection;

final class RouteTreeLoader
{
	public function __construct (
		private readonly RouteTreeCollectionBuilder $builder,
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

		return $this->builder->build($this->router->getRouteCollection());
	}

}
