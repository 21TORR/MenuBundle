<?php declare(strict_types=1);

namespace Torr\MenuBundle\Exception;

class InvalidTargetException extends MenuException
{
	/**
	 * @inheritDoc
	 */
	public function __construct ($target, ?\Throwable $previous = null)
	{
		parent::__construct(\sprintf(
			"Invalid target, must be RouteTarget or string, but %s given.",
			get_debug_type($target),
		), $previous);
	}
}
