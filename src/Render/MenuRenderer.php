<?php declare(strict_types=1);

namespace Torr\MenuBundle\Render;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Torr\HtmlBuilder\Builder\HtmlBuilder;
use Torr\HtmlBuilder\Node\HtmlElement;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Item\ResolvedMenuItem;
use Torr\MenuBundle\Render\Options\RenderOptions;
use Torr\MenuBundle\Resolver\MenuResolver;
use Torr\MenuBundle\Voter\Data\ActiveState;
use Torr\Rad\Route\LinkableInterface;

class MenuRenderer
{
	/**
	 * @param iterable<ItemRenderVisitorInterface> $renderVisitors
	 */
	public function __construct(
		private readonly MenuResolver $menuResolver,
		private readonly UrlGeneratorInterface $urlGenerator,
		private readonly TranslatorInterface $translator,
		private readonly iterable $renderVisitors = [],
	) {}


	/**
	 * Renders the tree from the given root
	 */
	public function render (?MenuItem $root, ?RenderOptions $renderOptions = null) : string
	{
		if (null === $root)
		{
			return "";
		}

		$renderOptions ??= new RenderOptions();
		$resolvedRoot = $this->menuResolver->resolveMenu($root);

		$topLevel = new HtmlElement("ul", [
			"class" => $renderOptions->rootClass,
		]);
		ray($resolvedRoot);

		foreach ($resolvedRoot->getVisibleChildren() as $child)
		{
			$li = new HtmlElement("li");
			$this->renderAndAppendElement(
				$li,
				$child,
				$renderOptions,
				0,
			);

			$topLevel->append($li);
		}

		$this->visitItemAfterRender($resolvedRoot, $topLevel);

		return (new HtmlBuilder())->build($topLevel);
	}


	/**
	 */
	private function renderAndAppendElement (
		HtmlElement $parentListElement,
		ResolvedMenuItem $resolvedMenuItem,
		RenderOptions $options,
		int $depth,
	) : void
	{
		$menuItem = $resolvedMenuItem->getMenuItem();
		$target = $menuItem->getTarget();

		if (null !== $target)
		{
			$link = new HtmlElement("a", [
				"href" => $target instanceof LinkableInterface
					? $target->generateUrl($this->urlGenerator)
					: $target,
			]);
		}
		else
		{
			$link = new HtmlElement("span");
		}

		// region Label
		$label = $menuItem->getLabel();

		if ($label instanceof TranslatableInterface)
		{
			$label = $label->trans($this->translator, $options->locale);
		}

		$link->append($label);
		// endregion

		$classList = $link->getClassList();

		// add active class
		switch ($resolvedMenuItem->getActiveState())
		{
			case ActiveState::ACTIVE:
				$classList->add($options->currentClass);
				break;

			case ActiveState::ACTIVE_ANCESTOR:
				$classList->add($options->ancestorClass);
				break;
		}

		// append to parent element
		$parentListElement->append($link);

		$children = $resolvedMenuItem->getVisibleChildren();
		$nextDepth = $depth + 1;

		// render children
		if (!empty($children) && (null === $options->maxDepth || $nextDepth <= $options->maxDepth))
		{
			$childList = new HtmlElement("ul");

			foreach ($children as $child)
			{
				$childListElement = new HtmlElement("li");

				$this->renderAndAppendElement(
					$childListElement,
					$child,
					$options,
					$nextDepth,
				);

				$childList->append($childListElement);
			}

			if (null !== $options->levelClass)
			{
				$childList->getClassList()->add(\sprintf($options->levelClass, $nextDepth));
			}

			$parentListElement->append($childList);
		}

		$this->visitItemAfterRender($resolvedMenuItem, $link);
	}

	/**
	 * Runs the render visitors on the menu item
	 */
	private function visitItemAfterRender (ResolvedMenuItem $item, HtmlElement $element) : void
	{
		foreach ($this->renderVisitors as $visitor)
		{
			$visitor->renderItem($item, $element);
		}
	}
}
