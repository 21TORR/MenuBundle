<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\RouteTree\Options;

use PHPUnit\Framework\TestCase;
use Torr\MenuBundle\RouteTree\Options\RouteTreeOptions;

final class RouteTreeOptionsTest extends TestCase
{
	/**
	 *
	 */
	public function provideFromArray () : iterable
	{
		yield "empty" => [[], new RouteTreeOptions()];
		yield "full" => [["translationDomain" => "domain"], new RouteTreeOptions("domain")];
		yield "ignore additional keys" => [["translationDomain" => "domain", "other" => "test"], new RouteTreeOptions("domain")];
	}

	/**
	 * @dataProvider provideFromArray
	 */
	public function testFromArray (array $data, RouteTreeOptions $expected) : void
	{
		$actual = RouteTreeOptions::fromArray($data);
		self::assertEquals($expected, $actual);
	}
}
