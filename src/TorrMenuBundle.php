<?php declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Torr\BundleHelpers\Bundle\BundleExtension;
use Torr\MenuBundle\Render\ItemRenderVisitorInterface;
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
		$container->registerForAutoconfiguration(ItemRenderVisitorInterface::class)
			->addTag("torr.menu.visitor.render");

		$container->registerForAutoconfiguration(VoterInterface::class)
			->addTag("torr.menu.voter");
	}
}
