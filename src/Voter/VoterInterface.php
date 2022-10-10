<?php declare(strict_types=1);

namespace Torr\MenuBundle\Voter;

use Torr\MenuBundle\Item\MenuItem;

interface VoterInterface
{
	/**
	 * Checks whether an item is current.
	 */
	public function vote (MenuItem $item) : bool;
}
