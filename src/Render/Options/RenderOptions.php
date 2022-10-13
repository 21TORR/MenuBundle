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
		 * The locale to translate the labels to
		 */
		public readonly ?string $locale = null,
	) {}

	/**
	 *
	 */
	public static function fromArray (array $data) : self
	{
		$filtered = [];
		$definedProperties = get_object_vars(new self());

		foreach ($data as $key => $value)
		{
			if (\array_key_exists($key, $definedProperties))
			{
				$filtered[$key] = $value;
			}
		}

		return new self(...$filtered);
	}
}
