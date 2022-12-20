<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\RouteTree\Transformer;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Torr\MenuBundle\Exception\MissingOptionalDependencyException;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\RouteTree\Transformer\RouteTreeTransformHelper;

final class RouteTreeTransformHelperTest extends TestCase
{
	/**
	 */
	public function testTranslate () : void
	{
		$translator = $this->createMock(TranslatorInterface::class);

		$translator
			->expects(self::once())
			->method("trans")
			->with("translation-key", [], "domain")
			->willReturn("translated");

		$helper = new RouteTreeTransformHelper($translator);
		$translated = $helper->translate("translation-key", "domain");
		self::assertSame("translated", $translated);

		self::assertNull($helper->translate(null, null));
	}

	/**
	 */
	public function testWithoutSecurityWithLabelWithoutTranslation () : void
	{
		$helper = new RouteTreeTransformHelper();
		self::assertSame(
			"label",
			$helper->translate("label", null),
		);
	}

	/**
	 */
	public function testWithoutSecurityWithLabelWithTranslation () : void
	{
		$this->expectException(MissingOptionalDependencyException::class);

		$helper = new RouteTreeTransformHelper();
		$helper->translate("label", "domain");
	}

	/**
	 */
	public function testWithoutSecurityWithoutLabelWithTranslation () : void
	{
		$helper = new RouteTreeTransformHelper();
		self::assertNull($helper->translate(null, "domain"));
	}

	/**
	 */
	public function testWithoutSecurityWithNull () : void
	{
		$helper = new RouteTreeTransformHelper();
		self::assertNull($helper->translate(null, null));
	}

	/**
	 */
	public function testSecurity () : void
	{
		$security = $this->createMock(Security::class);

		$security
			->expects(self::once())
			->method("isGranted")
			->with("security-expression")
			->willReturn(true);

		$helper = new RouteTreeTransformHelper(security: $security);
		self::assertTrue($helper->isAccessible("security-expression"));
	}

	/**
	 */
	public function testSecurityWithNull () : void
	{
		$security = $this->createMock(Security::class);

		$security
			->expects(self::never())
			->method("isGranted");

		$helper = new RouteTreeTransformHelper(security: $security);
		self::assertTrue($helper->isAccessible(null));
	}

	/**
	 */
	public function testSecurityWithoutServiceAndWithNull () : void
	{
		$helper = new RouteTreeTransformHelper();
		self::assertTrue($helper->isAccessible(null));
	}

	/**
	 */
	public function testSort () : void
	{
		$items = [
			$item1 = new MenuItem("xyz"),
			$item2 = new MenuItem("abc"),
			$item3 = new MenuItem("def"),
			$item4 = new MenuItem("def", extras: ["priority" => 10]),
			$item5 = new MenuItem(null, extras: ["priority" => 9]),
			$item6 = new MenuItem(null),
		];

		$helper = new RouteTreeTransformHelper();

		self::assertEquals(
			[$item4, $item5, $item6, $item2, $item3, $item1],
			$helper->sortMenuItems($items)
		);
	}
}
