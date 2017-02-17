<?php
namespace Declarative;

/**
 * Stores the current catalogue of Resources as they are declared, and organises them for execution.
 * Type function calls should get access to the static instance of the Catalogue (or use($catalogue)) and call
 * addResource.
 * 
 * There are effectively three passes:
 *  1. Resources are added. Duplicates by name will cause Exceptions to be thrown.
 *  2. Dependencies are checked. Anything depending on an undefined resource will cause an Exception to be thrown.
 *  3. ExecutionTree is built. Any dependency cycles or un-resolvable statements will cause Exceptions to be thrown.
 *
 * At this point ExecutionTree can run - starting from the Root Node each Resource will be executed. On CHANGE any
 * children will be executed in turn. On NO_CHANGE children will be ignored. On ERROR children will be marked as
 * skipped.
 */
class Catalogue
{
	private static $instance;
	private $resources;

	public function __construct()
	{
		static::$instance = $this;
	}

	public static function getInstance()
	{
		if(static::$instance === null) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	public function addResource(Resource $resource)
	{
		$name = $resource->getFullName();
		
		if(isset($this->resources[$name])) {
			throw new \Exception("Resource $name is already defined!");
		}

		$this->resources[$name] = $resource;
	}
}