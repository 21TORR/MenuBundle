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
	public function vote (MenuItem $item) : ?bool
	{
		$request = $this->requestStack->getMainRequest();

		if (null === $request)
		{
			return null;
		}

		$route = $request->attributes->get("_route");

		if (null === $route)
		{
			return null;
		}

		$target = $item->getTarget();

		// if the target is not a linkable, just skip
		if (!$target instanceof Linkable)
		{
			return null;
		}

		if ($target->getRoute() !== $route)
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
	private function checkParameters (array $left, array $right) : bool
	{
		if (\count($left) === \count($right))
		{
			foreach ($left as $key => $value)
			{
				if (!\array_key_exists($key, $right) || $right[$key] !== $value)
				{
					return false;
				}
			}
		}

		return true;
	}
}
