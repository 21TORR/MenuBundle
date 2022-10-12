<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Transformer;

use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Torr\MenuBundle\Exception\MissingDependencyException;
use Torr\MenuBundle\Item\MenuItem;

final class RouteTreeTransformHelper
{
	/**
	 */
	public function __construct (
		private readonly ?TranslatorInterface $translator,
		private readonly ?Security $security,
	) {}


	/**
	 *
	 */
	public function translate (
		?string $label,
		?string $translationDomain,
	) : ?string
	{
		if (null === $label || null === $translationDomain)
		{
			return $label;
		}

		if (null === $this->translator)
		{
			throw new MissingDependencyException("Can't use translatable routes without a translator.");
		}

		return $this->translator->trans($label, domain: $translationDomain);
	}


	/**
	 * Sorts the given menu items
	 *
	 * @return MenuItem[]
	 */
	public function sortMenuItems (array $items) : array
	{
		\usort(
			$items,
			static function (MenuItem $left, MenuItem $right) : int
			{
				// order by priority desc by default
				$priorityDifference = $right->getExtra("priority", 0) - $left->getExtra("priority", 0);

				// if the same priority -> sort alphabetically asc
				if (0 === $priorityDifference)
				{
					$leftLabel = $left->getLabel();
					$rightLabel = $right->getLabel();

					// the route tree only generates already translated menu items
					\assert(null === $leftLabel || \is_string($leftLabel));
					\assert(null === $rightLabel || \is_string($rightLabel));

					return \strnatcasecmp((string) $leftLabel, (string) $rightLabel);
				}

				return $priorityDifference;
			},
		);

		return $items;
	}


	/**
	 * Returns whether the security expression is currently accessible
	 */
	public function isAccessible (?string $security) : bool
	{
		if (null === $security)
		{
			return true;
		}

		if (null === $this->security)
		{
			throw new MissingDependencyException("Can't use security settings if the security bundle is not installed.");
		}

		return $this->security->isGranted($security);
	}
}
