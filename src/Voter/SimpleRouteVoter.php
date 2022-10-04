<?php declare(strict_types=1);

namespace Torr\MenuBundle\Voter;

use Symfony\Component\HttpFoundation\RequestStack;
use Torr\MenuBundle\Item\MenuItem;
use Torr\Rad\Route\Linkable;

/**
 * Simple voter that just checks whether the route of the item matches to the current route.
 */
class SimpleRouteVoter implements VoterInterface
{
	/**
	 */
	public function __construct(
		private readonly RequestStack $requestStack,
		private readonly bool $alsoCheckParameters = false,
	) {}

	/**
	 * @inheritDoc
	 */
	public function vote (MenuItem $item) : bool
	{
		$request = $this->requestStack->getMainRequest();

		if (null === $request)
		{
			return false;
		}

		$route = $request->attributes->get("_route");

		if (null === $route)
		{
			return false;
		}

		$target = $item->getTarget();

		// if the target is not a linkable, just skip
		if (!$target instanceof Linkable || $target->getRoute() !== $route)
		{
			return false;
		}

		return !$this->alsoCheckParameters || $this->checkParameters(
			$request->attributes->get("_route_params"),
			$target->getParameters(),
		);
	}


	/**
	 * Checks that the parameters are equal.
	 */
	private function checkParameters (array $currentActiveParams, array $linkableParams) : bool
	{
		// The linkable can have more params than we currently have.
		// For this check to be executed, the route must already match -> that means it's safe to assume
		// that additional params in the linkable are for query params.
		foreach ($currentActiveParams as $key => $value)
		{
			if (!\array_key_exists($key, $linkableParams) || $linkableParams[$key] !== $value)
			{
				return false;
			}
		}

		return true;
	}
}
