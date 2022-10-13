<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\Render\Options;

use PHPUnit\Framework\TestCase;
use Torr\MenuBundle\Render\Options\RenderOptions;

final class RenderOptionsTest extends TestCase
{
	/**
	 *
	 */
	public function provideFromArray () : iterable
	{
		yield "empty" =>  [[], new RenderOptions()];
		yield "full" => [
			[
				"rootClass" => "rootClass",
				"currentClass" => "currentClass",
				"ancestorClass" => "ancestorClass",
				"maxDepth" => 11,
				"levelClass" => "levelClass",
				"locale" => "locale",
			],
			new RenderOptions(
				rootClass: "rootClass",
				currentClass: "currentClass",
				ancestorClass: "ancestorClass",
				maxDepth: 11,
				levelClass: "levelClass",
				locale: "locale",
			),
		];
		yield "ignore additional keys" => [["rootClass" => "rootClass", "other" => "test"], new RenderOptions("rootClass")];
	}

	/**
	 * @dataProvider provideFromArray
	 */
	public function testFromArray (array $data, RenderOptions $expected) : void
	{
		$actual = RenderOptions::fromArray($data);
		self::assertEquals($expected, $actual);
	}
}
