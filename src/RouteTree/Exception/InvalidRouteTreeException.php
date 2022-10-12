<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Exception;

use Torr\MenuBundle\Exception\MenuException;

final class InvalidRouteTreeException extends \RuntimeException implements MenuException
{
}
