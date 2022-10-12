<?php declare(strict_types=1);

namespace Torr\MenuBundle\Twig;

use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Render\MenuRenderer;
use Torr\MenuBundle\Render\Options\RenderOptions;
use Torr\MenuBundle\RouteTree\Options\RouteTreeOptions;
use Torr\MenuBundle\RouteTree\Render\RouteMenuRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MenuTwigExtension extends AbstractExtension
{
	/**
	 */
	public function __construct (
		private readonly MenuRenderer $menuRenderer,
		private readonly RouteMenuRenderer $routeMenuRenderer,
	) {}


	public function renderMenu (MenuItem $item, array $options = []) : string
	{
		return $this->menuRenderer->render($item, RenderOptions::fromArray($options));
	}

	public function renderRouteMenu (string $routeName, array $options = []) : string
	{
		return $this->routeMenuRenderer->render(
			$routeName,
			RouteTreeOptions::fromArray($options),
			RenderOptions::fromArray($options),
		);
	}


	/**
	 * @inheritDoc
	 */
	public function getFunctions () : array
	{
		$safeHtml = ["is_safe" => ["html"]];

		return [
			new TwigFunction("menu_render", $this->renderMenu(...), $safeHtml),
			new TwigFunction("route_menu_render", $this->renderRouteMenu(...), $safeHtml),
		];
	}
}
