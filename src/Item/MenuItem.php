<?php declare(strict_types=1);

namespace Torr\MenuBundle\Item;

use Symfony\Contracts\Translation\TranslatableInterface;
use Torr\Rad\Route\LinkableInterface;

class MenuItem
{
	// region Fields
	/**
	 * The children of the menu item.
	 *
	 * @var self[]
	 */
	private array $children = [];
	//endregion


	/**
	 */
	public function __construct (
		/**
		 * The label to display.
		 * Will be translated using the translation domain given in the renderer.
		 */
		private readonly TranslatableInterface|string|null $label = null,
		/**
		 * The parent menu item.
		 */
		private ?self $parent = null,
		/**
		 * The target of this item.
		 *
		 * LinkableInterface	-> route
		 * string				-> direct URI
		 * null					-> no link
		 */
		private readonly LinkableInterface|string|null $target = null,
		/**
		 * Whether the item is the currently selected menu item.
		 */
		private bool $current = false,
		/**
		 * The extra attributes on the menu item.
		 */
		private array $extras = [],
		/**
		 * Whether the item is virtual (= should be included in the tree but never rendered).
		 */
		private bool $visible = true,
	)
	{
		$this->parent?->addChild($this);

		if (null === $this->label)
		{
			$this->visible = false;
		}
	}


	//region Accessors
	/**
	 */
	public function getParent () : ?self
	{
		return $this->parent;
	}


	/**
	 */
	public function getLabel () : TranslatableInterface|string|null
	{
		return $this->label;
	}


	/**
	 *
	 */
	public function setParent (?self $parent) : self
	{
		// remove child from previous parent
		$this->parent?->removeChild($this);

		// update parent
		$this->parent = $parent;

		// add to parent children, if parent is not null
		$parent?->addChild($this);

		return $this;
	}


	/**
	 */
	public function getTarget () : LinkableInterface|string|null
	{
		return $this->target;
	}


	/**
	 */
	public function setExtra (string $name, mixed $value) : self
	{
		$this->extras[$name] = $value;
		return $this;
	}


	/**
	 */
	public function getExtra (string $name, mixed $defaultValue = null) : mixed
	{
		return $this->extras[$name] ?? $defaultValue;
	}


	/**
	 */
	public function isVisible () : bool
	{
		return $this->visible;
	}

	/**
	 */
	public function isCurrent () : bool
	{
		return true === $this->current;
	}


	/**
	 */
	public function setCurrent (bool $current = true) : self
	{
		$this->current = $current;
		return $this;
	}


	/**
	 * @return self[]
	 */
	public function getChildren () : array
	{
		return $this->children;
	}


	/**
	 *
	 */
	public function addChild (self $child) : self
	{
		$child->parent?->removeChild($child);

		$child->parent = $this;
		$this->children[] = $child;
		return $this;
	}


	/**
	 *
	 */
	public function removeChild (self $childToRemove) : self
	{
		$newChildren = [];

		foreach ($this->children as $child)
		{
			if ($child === $childToRemove)
			{
				$child->parent = null;
				continue;
			}

			$newChildren[] = $child;
		}

		$this->children = $newChildren;

		return $this;
	}


	/**
	 *
	 */
	public function __clone ()
	{
		// Remove the parent link when cloning, as it wouldn't even be in the list of children.
		// If the user wants to add it to the same parent, they can do it themselves.
		// This has the added bonus that if used with `find()` and rendering, that it will reset
		// the level calculation on this node.
		$this->parent = null;

		// Explicitly deep clone children.
		$oldChildren = $this->children;
		$this->children = [];

		foreach ($oldChildren as $child)
		{
			$this->addChild(clone $child);
		}
	}


	/**
	 * @return self[]
	 */
	public function getVisibleChildren () : array
	{
		$filtered = [];

		foreach ($this->children as $child)
		{
			if ($child->isVisible())
			{
				$filtered[] = $child;
			}
		}

		return $filtered;
	}
}
