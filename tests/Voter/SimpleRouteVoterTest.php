<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Voter\SimpleRouteVoter;
use Torr\Rad\Route\Linkable;

final class SimpleRouteVoterTest extends TestCase
{
	/**
	 *
	 */
	public function provideMatches () : iterable
	{
		yield "simple match" => [
			true,
			new MenuItem(target: new Linkable("route")),
			"route",
		];

		yield "simple mismatch" => [
			false,
			new MenuItem(target: new Linkable("route")),
			"other-route",
		];

		yield "simple parameter" => [
			false,
			new MenuItem(target: new Linkable("route", ["a" => 1])),
			"other-route",
			true,
			["a" => 1],
		];

		yield "linkable has more parameters" => [
			true,
			new MenuItem(target: new Linkable("route", ["a" => 1, "b" => 2])),
			"route",
			true,
			["a" => 1],
		];

		yield "route has more parameters" => [
			false,
			new MenuItem(target: new Linkable("route", ["a" => 1])),
			"route",
			true,
			["a" => 1, "b" => 2],
		];

		yield "route has more parameters, but don't check params" => [
			true,
			new MenuItem(target: new Linkable("route", ["a" => 1])),
			"route",
			false,
			["a" => 1, "b" => 2],
		];
	}


	/**
	 * @dataProvider provideMatches
	 */
	public function testMatches (
		bool $expected,
		MenuItem $item,
		string $currentRoute,
		bool $alsoCheckParameters = false,
		array $currentRouteParameters = [],
	) : void
	{
		$requestStack = new RequestStack();
		$requestStack->push(
			new Request(attributes: [
				"_route" => $currentRoute,
				"_route_params" => $currentRouteParameters,
			])
		);

		$voter = new SimpleRouteVoter($requestStack, $alsoCheckParameters);
		self::assertSame($expected, $voter->vote($item));
	}
}
