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
use Torr\Rad\Route\Linkable;

final class MenuRendererTest extends TestCase
{
	/**
	 *
	 */
	public function testRender () : void
	{
		$tree = (new MenuItem())
			->addChild(
				(new MenuItem("Level 1"))
					->addChild(new MenuItem("Level 1.1"))
					->addChild(new MenuItem("Level 1.2"))
			)
			->addChild(
				(new MenuItem("Level 2"))
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
			->addChild(new MenuItem("Fixed"))
			->addChild(new MenuItem(new TranslatableMessage("translation-key", ["some" => "param"], "domain")))
			->addChild(new MenuItem(null));

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


	/**
	 * Test target generation
	 */
	public function testTargets () : void
	{
		$tree = (new MenuItem())
			->addChild(new MenuItem("Null", target: null))
			->addChild(new MenuItem("Fixed", target: "fixed"))
			->addChild(new MenuItem("Linkable", target: new Linkable("route", ["some" => "params"], UrlGeneratorInterface::ABSOLUTE_URL)));

		$urlGenerator = $this->createMock(UrlGeneratorInterface::class);
		$translator = $this->createMock(TranslatorInterface::class);

		$urlGenerator
			->method("generate")
			->with("route", ["some" => "params"], UrlGeneratorInterface::ABSOLUTE_URL)
			->willReturn("generated");

		$renderer = new MenuRenderer(new MenuResolver(), $urlGenerator, $translator);
		$result = $renderer->render($tree);

		$expected = $this->removeWhitespace(<<<'HTML'
			<ul>
				<li>
					<span>Null</span>
				</li>
				<li>
					<a href="fixed">Fixed</a>
				</li>
				<li>
					<a href="generated">Linkable</a>
				</li>
			</ul>
		HTML);

		self::assertSame($expected, $result);
	}


	/**
	 * Locale is already covered in {@see testTranslate()}
	 */
	public function testRenderOptions () : void
	{
		$tree = (new MenuItem())
			->addChild(
				(new MenuItem("Level 1"))
					->addChild(
						(new MenuItem("Level 1.1"))
							->addChild(
								(new MenuItem("Level 1.1.1"))
									->addChild(new MenuItem("Level 1.1.1.1"))
							)
							->addChild(new MenuItem("Level 1.1.2"))
					)
					->addChild(
						(new MenuItem("Level 1.2"))
							->addChild(new MenuItem("Level 1.2.1"))
							->addChild(new MenuItem("Level 1.2.2"))
					)
			)
			->addChild(
				(new MenuItem("Level 2"))
					->addChild(
						(new MenuItem("Level 2.1"))
							->addChild(new MenuItem("Level 2.1.1"))
							->addChild(new MenuItem("Level 2.1.2"))
					)
					->addChild(
						(new MenuItem("Level 2.2"))
							->addChild(new MenuItem("Level 2.2.1"))
							->addChild(new MenuItem("Level 2.2.2"))
					)
			);

		$renderOptions = new RenderOptions(
			rootClass: "root-class",
			maxDepth: 2,
			levelClass: "level-%d",
		);
		$renderer = new MenuRenderer(new MenuResolver());
		$result = $renderer->render($tree, $renderOptions);

		$expected = $this->removeWhitespace(<<<'HTML'
			<ul class="root-class">
				<li>
					<span>Level 1</span>
					<ul class="level-1">
						<li>
							<span>Level 1.1</span>
							<ul class="level-2">
								<li><span>Level 1.1.1</span></li>
								<li><span>Level 1.1.2</span></li>
							</ul>
						</li>
						<li>
							<span>Level 1.2</span>
							<ul class="level-2">
								<li><span>Level 1.2.1</span></li>
								<li><span>Level 1.2.2</span></li>
							</ul>
						</li>
					</ul>
				</li>
				<li>
					<span>Level 2</span>
					<ul class="level-1">
						<li>
							<span>Level 2.1</span>
							<ul class="level-2">
								<li><span>Level 2.1.1</span></li>
								<li><span>Level 2.1.2</span></li>
							</ul>
						</li>
						<li>
							<span>Level 2.2</span>
							<ul class="level-2">
								<li><span>Level 2.2.1</span></li>
								<li><span>Level 2.2.2</span></li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>
		HTML);

		self::assertSame($expected, $result);
	}

	/**
	 *
	 */
	public function testCurrent () : void
	{
		$tree = (new MenuItem())
			->addChild(
				(new MenuItem("Level 1"))
					->addChild(
						(new MenuItem("Level 1.1"))
							->addChild(
								(new MenuItem("Level 1.1.1"))
									->setCurrent()
									->addChild(new MenuItem("Level 1.1.1.1"))
							)
					)
			);

		$renderOptions = new RenderOptions(
			currentClass: "is-current-custom",
			ancestorClass: "is-current-ancestor-custom",
		);
		$renderer = new MenuRenderer(new MenuResolver());
		$result = $renderer->render($tree, $renderOptions);

		$expected = $this->removeWhitespace(<<<'HTML'
			<ul>
				<li>
					<span class="is-current-ancestor-custom">Level 1</span>
					<ul>
						<li>
							<span class="is-current-ancestor-custom">Level 1.1</span>
							<ul>
								<li>
									<span class="is-current-custom">Level 1.1.1</span>
									<ul>
										<li>
											<span>Level 1.1.1.1</span>
										</li>
									</ul>
								</li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>
		HTML);

		self::assertSame($expected, $result);
	}
	/**
	 *
	 */
	private function removeWhitespace (string $text) : string
	{
		return \implode(
			"",
			\array_map("trim", \explode("\n", $text))
		);
	}
}
