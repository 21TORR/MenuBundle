<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\Twig;

use PHPUnit\Framework\TestCase;
use Torr\MenuBundle\Render\MenuRenderer;
use Torr\MenuBundle\RouteTree\Render\RouteMenuRenderer;
use Torr\MenuBundle\Twig\MenuTwigExtension;
use Twig\TwigFunction;

final class MenuTwigExtensionTest extends TestCase
{
	/**
	 */
	public function testExposedFunctions () : void
	{
		$extension = new MenuTwigExtension(
			$this->createMock(MenuRenderer::class),
			$this->createMock(RouteMenuRenderer::class),
		);

		$names = \array_map(
			static fn (TwigFunction $function) => $function->getName(),
			$extension->getFunctions(),
		);

		self::assertContains("menu_render", $names);
		self::assertContains("route_menu_render", $names);
	}
}
