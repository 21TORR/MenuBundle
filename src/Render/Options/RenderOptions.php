<?php declare(strict_types=1);

namespace Torr\MenuBundle\Render\Options;

final class RenderOptions
{
	/**
	 */
	public function __construct (
		public readonly ?string $rootClass = null,
		public readonly string $currentClass = "is-current",
		public readonly string $ancestorClass = "is-current-ancestor",
		public readonly ?int $maxDepth = null,
		/**
		 * The pattern to generate the level class. Is used in `sprintf` and is passed the level
		 */
		public readonly ?string $levelClass = null,
		/**
		 * The locale to translated the labels to
		 */
		public readonly ?string $locale = null,
	) {}
}
