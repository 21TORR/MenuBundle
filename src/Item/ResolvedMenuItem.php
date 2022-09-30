<?php declare(strict_types=1);

namespace Torr\MenuBundle\Item;

use Torr\MenuBundle\Voter\Data\ActiveState;

final class ResolvedMenuItem
{
	private ActiveState $activeState = ActiveState::INACTIVE;
	/** @var ResolvedMenuItem[] */
	private array $children = [];

	/**
	 */
	public function __construct (
		private readonly ?self $parent,
		private readonly MenuItem $menuItem,
	)
	{
		if (null !== $this->parent)
		{
			$this->parent->children[] = $this;
		}
	}

	/**
	 */
	public function getParent () : ?self
	{
		return $this->parent;
	}

	/**
	 */
	public function getMenuItem () : MenuItem
	{
		return $this->menuItem;
	}

	/**
	 */
	public function setActive () : void
	{
		$this->activeState = ActiveState::ACTIVE;
		$this->parent?->updateActiveAncestor();
	}

	/**
	 */
	private function updateActiveAncestor () : void
	{
		if ($this->activeState->value < ActiveState::ACTIVE_ANCESTOR)
		{
			$this->activeState = ActiveState::ACTIVE_ANCESTOR;
		}

		$this->parent?->updateActiveAncestor();
	}

	/**
	 */
	public function getActiveState () : ActiveState
	{
		return $this->activeState;
	}

	/**
	 * @return ResolvedMenuItem[]
	 */
	public function getChildren () : array
	{
		return $this->children;
	}

	/**
	 * @return ResolvedMenuItem[]
	 */
	public function getVisibleChildren () : array
	{
		$children = [];

		foreach ($this->children as $child)
		{
			if ($child->menuItem->isVisible())
			{
				$children[] = $child;
			}
		}

		return $children;
	}
}
