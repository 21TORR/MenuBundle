<?php declare(strict_types=1);

namespace Torr\MenuBundle\Render;

use Torr\HtmlBuilder\Node\HtmlElement;
use Torr\MenuBundle\Item\ResolvedMenuItem;

/**
 * Renders the menu item
 */
interface ItemRenderVisitorInterface
{
	/**
	 * Renders the HTML for the given item
	 */
	public function renderItem (
		ResolvedMenuItem $item,
		HtmlElement $element,
		int $depth,
	) : void;
}
