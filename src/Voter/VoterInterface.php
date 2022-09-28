<?php declare(strict_types=1);

namespace Torr\MenuBundle\Voter;

use Torr\MenuBundle\Item\MenuItem;

interface VoterInterface
{
	/**
	 * Checks whether an item is current.
	 * If the voter is unable to decide it should abstain a vote and return `null`.
	 */
	public function vote (MenuItem $item) : ?bool;
}
