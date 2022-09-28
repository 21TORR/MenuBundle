<?php declare(strict_types=1);

namespace Torr\MenuBundle\Twig;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Torr\MenuBundle\Item\MenuItem;
use Torr\MenuBundle\Renderer\MenuRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MenuTwigExtension extends AbstractExtension implements ServiceSubscriberInterface
{
	/**
	 */
	public function __construct(
		private readonly ContainerInterface $locator,
	)
	{
	}


	/**
	 */
	public function renderMenu (?MenuItem $root, array $options = []) : string
	{
		return $this->locator->get(MenuRenderer::class)->render($root, $options);
	}


	/**
	 * @inheritDoc
	 */
	public function getFunctions () : array
	{
		return [
			new TwigFunction("menu_render", [$this, "renderMenu"], ["is_safe" => ["html"]]),
		];
	}


	/**
	 * @inheritDoc
	 */
	public static function getSubscribedServices () : array
	{
		return [
			MenuRenderer::class,
		];
	}
}
