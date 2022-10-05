<?php declare(strict_types=1);

namespace Tests\Torr\MenuBundle\Resolver;

use PHPUnit\Framework\TestCase;
use Torr\MenuBundle\Item\Data\ActiveState;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Resolver\MenuResolver;
use Torr\MenuBundle\Voter\VoterInterface;

final class MenuResolverTest extends TestCase
{
	/**
	 *
	 */
	public function testSkipVotersAfterTrue () : void
	{
		$voter1 = $this->createMock(VoterInterface::class);
		$voter1
			->expects(self::once())
			->method("vote")
			->willReturn(true);

		$voter2 = $this->createMock(VoterInterface::class);
		$voter2
			->expects(self::never())
			->method("vote");

		$resolver = new MenuResolver([$voter1, $voter2]);
		$resolver->resolveMenu(new MenuItem());
	}

	/**
	 *
	 */
	public function testInvisibleCurrent () : void
	{
		$resolver = new MenuResolver();
		$resolved = $resolver->resolveMenu(
			(new MenuItem())
				->addChild(
					(new MenuItem(label: "Hi"))
						->addChild(
							new MenuItem(current: true)
						)
				)
		);

		self::assertSame(ActiveState::ACTIVE_ANCESTOR, $resolved->getActiveState());
	}


	/**
	 *
	 */
	public function testVoters () : void
	{
		$voter1 = $this->createMock(VoterInterface::class);
		$voter1
			->expects(self::exactly(4))
			->method("vote")
			->willReturn(false);

		$voter2 = $this->createMock(VoterInterface::class);
		$voter2
			->expects(self::exactly(4))
			->method("vote")
			->willReturn(false);

		$resolver = new MenuResolver([$voter1, $voter2]);
		$resolver->resolveMenu(
			(new MenuItem())
			->addChild(
				(new MenuItem())
					->addChild(new MenuItem())
			)
			->addChild(new MenuItem())
		);
	}


	/**
	 */
	public function testSkipVotersIfAlreadyCurrent () : void
	{
		$voter = $this->createMock(VoterInterface::class);
		$voter
			->expects(self::never())
			->method("vote");

		$resolver = new MenuResolver();
		$resolver->resolveMenu(new MenuItem(current: true));
	}

	/**
	 */
	public function testAncestor () : void
	{
		$root = (new MenuItem())
			->addChild(
				(new MenuItem())
					->addChild(new MenuItem(current: true))
			)
			->addChild(
				(new MenuItem())
					->addChild(new MenuItem(extras: ["active" => true]))
			);

		$voter = new class implements VoterInterface
		{
			public function vote (MenuItem $item) : bool
			{
				return $item->getExtra("active", false);
			}
		};

		$resolver = new MenuResolver([$voter]);
		$resolved = $resolver->resolveMenu($root);

		self::assertSame(ActiveState::ACTIVE_ANCESTOR, $resolved->getChildren()[0]->getActiveState());
		self::assertSame(ActiveState::ACTIVE_ANCESTOR, $resolved->getChildren()[1]->getActiveState());

		self::assertSame(ActiveState::ACTIVE, $resolved->getChildren()[0]->getChildren()[0]->getActiveState());
		self::assertSame(ActiveState::ACTIVE, $resolved->getChildren()[1]->getChildren()[0]->getActiveState());
	}

	/**
	 */
	public function testVisibleChildren () : void
	{
		$root = (new MenuItem())
			->addChild(new MenuItem(label: "test"))
			->addChild(new MenuItem());

		$resolver = new MenuResolver();
		$resolved = $resolver->resolveMenu($root);

		self::assertCount(2, $resolved->getChildren());
		self::assertCount(1, $resolved->getVisibleChildren());
	}
}
