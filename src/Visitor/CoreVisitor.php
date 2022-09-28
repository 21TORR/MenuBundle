<?php declare(strict_types=1);

namespace Torr\MenuBundle\Visitor;

use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Torr\MenuBundle\Exception\InvalidTargetException;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Target\LazyRoute;

/**
 * Core visitor, that checks the security and replaces the URLs.
 */
class CoreVisitor implements ItemVisitor
{
	/**
	 */
	public function __construct(
		private readonly UrlGeneratorInterface $urlGenerator,
		private readonly ?AuthorizationCheckerInterface $authorizationChecker,
		private readonly LoggerInterface $logger,
		private readonly bool $isDebug,
	)
	{
	}


	/**
	 * @inheritDoc
	 *
	 * @throws ExceptionInterface|InvalidTargetException
	 */
	public function visit (MenuItem $item, array $options) : void
	{
		$target = $item->getTarget();

		// check security
		if (null !== $this->authorizationChecker && null !== $item->getSecurity() && !$this->authorizationChecker->isGranted(new Expression($item->getSecurity())))
		{
			$item->setVisible(false);
		}

		// replace target with URL
		// do it after the security check, as it might hide the node
		if ($target instanceof LazyRoute)
		{
			// store the previous route in an extra attribute
			$item->setExtra("_route", $target->getRoute());
			$item->setExtra("_route_params", $target->getParameters());

			if ($item->isVisible())
			{
				try
				{
					$item->setTarget($target->generate($this->urlGenerator));
				}
				catch (ExceptionInterface $exception)
				{
					$this->logger->error("Failed to resolve LazyRoute in item {item}: {message}", [
						"item" => $item->getLabel(),
						"message" => $exception->getMessage(),
						"exception" => $exception,
					]);

					if ($this->isDebug)
					{
						throw $exception;
					}

					$item->setTarget(null);
				}
			}
		}
	}


	/**
	 * @inheritDoc
	 */
	public function supports (array $options) : bool
	{
		return true;
	}
}
