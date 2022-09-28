<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Torr\BundleHelpers\Bundle\BundleExtension;
use Torr\MenuBundle\Visitor\ItemVisitor;
use Torr\MenuBundle\Voter\VoterInterface;

class TorrMenuBundle extends Bundle
{
	/**
	 * @inheritDoc
	 */
	public function getContainerExtension () : ExtensionInterface
	{
		return new BundleExtension($this);
	}

	/**
	 * @inheritDoc
	 */
	public function getPath () : string
	{
		return \dirname(__DIR__);
	}

	/**
	 * @inheritDoc
	 */
	public function build (ContainerBuilder $container) : void
	{
		$container->registerForAutoconfiguration(ItemVisitor::class)
			->addTag("torr.menu.visitor");

		$container->registerForAutoconfiguration(VoterInterface::class)
			->addTag("torr.menu.voter");
	}
}
