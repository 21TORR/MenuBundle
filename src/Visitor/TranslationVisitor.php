<?php declare(strict_types=1);

namespace Torr\MenuBundle\Visitor;

use Symfony\Contracts\Translation\TranslatorInterface;
use Torr\MenuBundle\Item\MenuItem;

class TranslationVisitor implements ItemVisitor
{
	/**
	 */
	public function __construct(
		private readonly ?TranslatorInterface $translator,
	)
	{
	}


	/**
	 * @inheritDoc
	 */
	public function visit (MenuItem $item, array $options) : void
	{
		\assert(null !== $this->translator);
		$label = $item->getLabel();

		if (null !== $label)
		{
			$item->setLabel($this->translator->trans($label, [], $options["translationDomain"]));
		}
	}


	/**
	 * @inheritDoc
	 */
	public function supports (array $options) : bool
	{
		return null !== $options["translationDomain"] && null !== $this->translator;
	}
}
