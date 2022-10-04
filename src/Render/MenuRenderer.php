<?php declare(strict_types=1);

namespace Torr\MenuBundle\Render;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
		private readonly iterable $renderVisitors,
		private readonly MenuResolver $menuResolver,
		private readonly UrlGeneratorInterface $router,
	) {}


	/**
	 * Renders the tree from the given root
	 */
	public function render (?MenuItem $root, RenderOptions $renderOptions) : string
	{
		if (null === $root)
		{
			return "";
		}

		$resolvedItem = $this->menuResolver->resolveMenu($root);

		$topLevel = new HtmlElement("ul", [
			"class" => $renderOptions->rootClass,
		]);

		foreach ($resolvedItem->getChildren() as $child)
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

		$this->visitItemAfterRender($resolvedItem, $topLevel);

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
					? $target->generateUrl($this->router)
					: $target,
			]);
		}
		else
		{
			$link = new HtmlElement("span");
		}

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

		if (null !== $options->levelClass)
		{
			$classList->add(\sprintf($options->levelClass, $depth));
		}

		$children = $resolvedMenuItem->getChildren();

		// append to parent element
		$parentListElement->append($link);

		// render children
		if (!empty($children))
		{
			$childList = new HtmlElement("ul");

			foreach ($resolvedMenuItem->getVisibleChildren() as $child)
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
