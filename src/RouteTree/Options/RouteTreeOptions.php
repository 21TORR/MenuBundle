<?php declare(strict_types=1);

namespace Torr\MenuBundle\RouteTree\Options;

final class RouteTreeOptions
{
	/**
	 */
	public function __construct (
		private readonly ?string $translationDomain = null,
	) {}

	/**
	 */
	public function getTranslationDomain () : ?string
	{
		return $this->translationDomain;
	}

	/**
	 */
	public static function fromArray (array $data) : self
	{
		return new self(
			translationDomain: $data["translationDomain"] ?? null,
		);
	}
}
