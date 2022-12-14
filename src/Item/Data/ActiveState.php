<?php declare(strict_types=1);

namespace Torr\MenuBundle\Item\Data;

enum ActiveState : int
{
	case INACTIVE = 0;

	case ACTIVE_ANCESTOR = 1;

	case ACTIVE = 2;
}
