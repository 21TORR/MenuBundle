<?php declare(strict_types=1);

namespace Torr\MenuBundle\Resolver;

use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Item\ResolvedMenuItem;
use Torr\MenuBundle\Voter\VoterInterface;

final class MenuResolver
{
	public function __construct (
		/** @var iterable<VoterInterface> $itemVoters */
		private readonly iterable $itemVoters = [],
	) {}

	/**
	 */
	public function resolveMenu (MenuItem $root) : ResolvedMenuItem
	{
		return $this->resolveItem(null, $root);
	}

	/**
	 */
	private function resolveItem (
		?ResolvedMenuItem $parent,
		MenuItem $item,
	) : ResolvedMenuItem
	{
		$resolvedItem = new ResolvedMenuItem($parent, $item);

		if ($item->isCurrent() || $this->isActiveItem($item))
		{
			$resolvedItem->setActive();
		}

		foreach ($item->getVisibleChildren() as $child)
		{
			$this->resolveItem($resolvedItem, $child);
		}

		return $resolvedItem;
	}

	/**
	 */
	private function isActiveItem (MenuItem $item) : bool
	{
		foreach ($this->itemVoters as $voter)
		{
			if ($voter->vote($item))
			{
				return true;
			}
		}

		return false;
	}
}
