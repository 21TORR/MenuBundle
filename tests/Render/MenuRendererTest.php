<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\Render;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Render\MenuRenderer;
use Torr\MenuBundle\Render\Options\RenderOptions;
use Torr\MenuBundle\Resolver\MenuResolver;

final class MenuRendererTest extends TestCase
{
	/**
	 *
	 */
	public function testRender () : void
	{
		$tree = (new MenuItem())
			->addChild(
				(new MenuItem(label: "Level 1"))
					->addChild(new MenuItem(label: "Level 1.1"))
					->addChild(new MenuItem(label: "Level 1.2"))
			)
			->addChild(
				(new MenuItem(label: "Level 2"))
			);

		$urlGenerator = $this->createMock(UrlGeneratorInterface::class);
		$translator = $this->createMock(TranslatorInterface::class);

		$renderer = new MenuRenderer(new MenuResolver(), $urlGenerator, $translator);
		$result = $renderer->render($tree);

		$expected = $this->removeWhitespace(<<<'HTML'
			<ul>
				<li>
					<span>Level 1</span>
					<ul>
						<li>
							<span>Level 1.1</span>
						</li>
						<li>
							<span>Level 1.2</span>
						</li>
					</ul>
				</li>
				<li>
					<span>Level 2</span>
				</li>
			</ul>
		HTML);

		self::assertSame($expected, $result);
	}

	/**
	 * Test translations
	 */
	public function testTranslate () : void
	{
		$tree = (new MenuItem())
			->addChild(new MenuItem(label: "Fixed"))
			->addChild(new MenuItem(label: new TranslatableMessage("translation-key", ["some" => "param"], "domain")))
			->addChild(new MenuItem(label: null));

		$urlGenerator = $this->createMock(UrlGeneratorInterface::class);
		$translator = $this->createMock(TranslatorInterface::class);

		$translator
			->method("trans")
			->with("translation-key", ["some" => "param"], "domain", "locale")
			->willReturn("translated-label");

		$renderer = new MenuRenderer(new MenuResolver(), $urlGenerator, $translator);
		$result = $renderer->render(
			$tree,
			new RenderOptions(locale: "locale"),
		);

		$expected = $this->removeWhitespace(<<<'HTML'
			<ul>
				<li>
					<span>Fixed</span>
				</li>
				<li>
					<span>translated-label</span>
				</li>
			</ul>
		HTML);

		self::assertSame($expected, $result);
	}

	private function removeWhitespace (string $text) : string
	{
		return \implode(
			"",
			\array_map("trim", \explode("\n", $text))
		);
	}
}
