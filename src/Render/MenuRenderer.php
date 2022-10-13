<?php declare(strict_types=1);

namespace Torr\MenuBundle\Render;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Torr\HtmlBuilder\Builder\HtmlBuilder;
use Torr\HtmlBuilder\Node\HtmlElement;
use Torr\MenuBundle\Exception\MissingDependencyException;
use Torr\MenuBundle\Item\Data\ActiveState;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Item\ResolvedMenuItem;
use Torr\MenuBundle\Render\Options\RenderOptions;
use Torr\MenuBundle\Resolver\MenuResolver;
use Torr\Rad\Route\LinkableInterface;

/**
 * @final
 */
class MenuRenderer
{
	/**
	 * @param iterable<ItemRenderVisitorInterface> $renderVisitors
	 */
	public function __construct(
		private readonly MenuResolver $menuResolver,
		private readonly ?UrlGeneratorInterface $urlGenerator = null,
		private readonly ?TranslatorInterface $translator = null,
		private readonly iterable $renderVisitors = [],
	) {}


	/**
	 * Renders the tree from the given root
	 */
	public function render (
		?MenuItem $root,
		RenderOptions $renderOptions = new RenderOptions(),
	) : string
	{
		if (null === $root)
		{
			return "";
		}

		$resolvedRoot = $this->menuResolver->resolveMenu($root);

		$topLevel = new HtmlElement("ul", [
			"class" => $renderOptions->rootClass,
		]);

		foreach ($resolvedRoot->getVisibleChildren() as $child)
		{
			$li = new HtmlElement("li");
			$this->renderAndAppendElement(
				$li,
				$child,
				$renderOptions,
				1,
			);

			$topLevel->append($li);
		}

		$this->visitItemAfterRender($resolvedRoot, $topLevel, 0);

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
			$resolvedTarget = $target;

			if ($resolvedTarget instanceof LinkableInterface)
			{
				if (null === $this->urlGenerator)
				{
					throw new MissingDependencyException("Can't use linkable menu items without a URL generator.");
				}

				$resolvedTarget = $resolvedTarget->generateUrl($this->urlGenerator);
			}

			$link = new HtmlElement("a", [
				"href" => $resolvedTarget,
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
			if (null === $this->translator)
			{
				throw new MissingDependencyException("Can't use translatable menu items without a translator.");
			}

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

		// render children
		if (!empty($children) && (null === $options->maxDepth || $depth <= $options->maxDepth))
		{
			$childList = new HtmlElement("ul");

			foreach ($children as $child)
			{
				$childListElement = new HtmlElement("li");

				$this->renderAndAppendElement(
					$childListElement,
					$child,
					$options,
					$depth + 1,
				);

				$childList->append($childListElement);
			}

			if (null !== $options->levelClass)
			{
				$childList->getClassList()->add(\sprintf($options->levelClass, $depth));
			}

			$parentListElement->append($childList);
		}

		$this->visitItemAfterRender($resolvedMenuItem, $link, $depth);
	}

	/**
	 * Runs the render visitors on the menu item
	 */
	private function visitItemAfterRender (ResolvedMenuItem $item, HtmlElement $element, int $depth) : void
	{
		foreach ($this->renderVisitors as $visitor)
		{
			$visitor->renderItem($item, $element, $depth);
		}
	}
}
