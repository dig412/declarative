<?php
namespace Declarative;

/**
 * Dependency is effectively a Tree Node - it has 0 or 1 parents, and 0 to many children. You constrcut one to hold
 * a Resource, then add any Children to it as needed. Parents are handled automatically - when you call addChild on a
 * Dependency, it will call setParent() on its new child to set everything up.
 */
class Dependency
{
	private $resource;
	private $parent;
	private $children = [];

	public function __construct(ExecutableResource $resource)
	{
		$this->resource = $resource;
	}

	public function hasParent()
	{
		return $this->parent !== null;
	}

	private function setParent(Dependency $parent)
	{
		$this->parent = $parent;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function hasChildren()
	{
		return count($this->children) > 0;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function addChild(Dependency $child)
	{
		$this->children[] = $child;
		$child->setParent($this);
	}

	public function getResource()
	{
		return $this->resource;
	}
}