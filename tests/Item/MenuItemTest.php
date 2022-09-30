<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\Item;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatableMessage;
use Torr\MenuBundle\Item\MenuItem;

final class MenuItemTest extends TestCase
{
	/**
	 */
	public function testParent () : void
	{
		$parent = new MenuItem();
		$child = new MenuItem($parent);

		self::assertNull($parent->getParent());
		self::assertCount(1, $parent->getChildren());
		self::assertSame($child, $parent->getChildren()[0]);
		self::assertSame($parent, $child->getParent());
	}


	/**
	 */
	public function moveChild () : void
	{
		$oldParent = new MenuItem();
		$child = new MenuItem($oldParent);

		self::assertSame($oldParent, $child->getParent());
		self::assertCount(1, $oldParent->getChildren());

		$newParent = new MenuItem();
		$newParent->addChild($child);

		self::assertSame($newParent, $child->getParent());
		self::assertCount(1, $newParent->getChildren());
		self::assertCount(0, $oldParent->getChildren());
	}


	/**
	 */
	public function testVisibleChildren () : void
	{
		$visible = new MenuItem(label: "test");

		$parent = (new MenuItem())
			->addChild(new MenuItem())
			->addChild(new MenuItem())
			->addChild($visible)
			->addChild(new MenuItem(label: "test2", virtual: true));

		self::assertCount(4, $parent->getChildren());
		self::assertCount(1, $parent->getVisibleChildren());
		self::assertSame($visible, $parent->getVisibleChildren()[0]);
	}


	/**
	 */
	public function provideVisibility () : iterable
	{
		yield [false, new MenuItem()];
		yield [true, new MenuItem(label: "test")];
		yield [true, new MenuItem(label: new TranslatableMessage("test"))];
		yield [false, new MenuItem(virtual: true)];
		yield [false, new MenuItem(label: "test", virtual: true)];
		yield [false, new MenuItem(label: new TranslatableMessage("test"), virtual: true)];
	}


	/**
	 * @dataProvider provideVisibility
	 */
	public function testVisibility (bool $expected, MenuItem $item) : void
	{
		self::assertSame($expected, $item->isVisible());
	}
}
