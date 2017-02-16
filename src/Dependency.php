<?php
namespace Declarative;

class Dependency
{
	private $resource;
	private $children = [];

	public function __construct(Resource $resource)
	{
		$this->resource = $resource;
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
	}

	public function getResource()
	{
		return $this->resource;
	}
}