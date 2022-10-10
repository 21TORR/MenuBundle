<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\Render;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Torr\HtmlBuilder\Node\HtmlElement;
use Torr\MenuBundle\Exception\MissingDependencyException;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Item\ResolvedMenuItem;
use Torr\MenuBundle\Render\ItemRenderVisitorInterface;
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
						(new MenuItem("Level 1.1", current: true))
							->addChild(
								(new MenuItem("Level 1.1.1", current: true))
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
							<span class="is-current-custom">Level 1.1</span>
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
	public function testInvisibleChildrenAreSkipped () : void
	{
		$root = (new MenuItem())
			->addChild(new MenuItem())
			->addChild(
				(new MenuItem("Level 1"))
					->addChild(new MenuItem())
					->addChild(
						(new MenuItem("Level 2"))
							->addChild(new MenuItem())
					)
					->addChild(new MenuItem())
			);

		$renderer = new MenuRenderer(new MenuResolver());
		$actual = $renderer->render($root);

		$expected = $this->removeWhitespace(<<<'HTML'
			<ul>
				<li>
					<span>Level 1</span>
					<ul>
						<li>
							<span>Level 2</span>
						</li>
					</ul>
				</li>
			</ul>
		HTML);

		self::assertSame($expected, $actual);
	}


	/**
	 *
	 */
	public function testMissingOptionalUrlGenerator () : void
	{
		$this->expectException(MissingDependencyException::class);
		$this->expectExceptionMessage("Can't use linkable menu items without a URL generator.");

		$item = (new MenuItem())
			->addChild(new MenuItem(label: "Entry", target: new Linkable("test")));

		$renderer = new MenuRenderer(new MenuResolver());
		$renderer->render($item);
	}

	/**
	 *
	 */
	public function testMissingOptionalTranslator () : void
	{
		$this->expectException(MissingDependencyException::class);
		$this->expectExceptionMessage("Can't use translatable menu items without a translator.");

		$item = (new MenuItem())
			->addChild(new MenuItem(label: new TranslatableMessage("test")));

		$renderer = new MenuRenderer(new MenuResolver());
		$renderer->render($item);
	}


	public function testVisitors () : void
	{
		$root = (new MenuItem())
			->addChild(
				(new MenuItem("Item 1"))
					->addChild(new MenuItem("Item 1.1"))
			)
			->addChild(new MenuItem("Item 2"));

		$visitor = new class () implements ItemRenderVisitorInterface
		{
			private int $callCount = 0;

			public function renderItem (ResolvedMenuItem $item, HtmlElement $element, int $depth) : void
			{
				++$this->callCount;
				$element->getClassList()->add("custom-class-{$depth}");
			}

			/**
			 */
			public function getCallCount () : int
			{
				return $this->callCount;
			}
		};

		$renderer = new MenuRenderer(new MenuResolver(), renderVisitors: [$visitor]);
		$actual = $renderer->render($root);

		$expected = $this->removeWhitespace(<<<'HTML'
			<ul class="custom-class-0">
				<li>
					<span class="custom-class-1">Item 1</span>
					<ul>
						<li>
							<span class="custom-class-2">Item 1.1</span>
						</li>
					</ul>
				</li>
				<li><span class="custom-class-1">Item 2</span></li>
			</ul>
		HTML);

		self::assertSame($expected, $actual);
		self::assertSame(4, $visitor->getCallCount());
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
