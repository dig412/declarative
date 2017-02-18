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
 *  3. ExecutionPlan is built. Any dependency cycles or un-resolvable statements will cause Exceptions to be thrown.
 *
 * At this point ExecutionPlan can run - starting from the Root Node each Resource will be executed. On CHANGE any
 * children will be executed in turn. On NO_CHANGE children will be ignored. On ERROR children will be marked as
 * skipped.
 */
class Catalogue
{
	/**
	 * Internal instance pointer, to ensure that there is one global instance of Catalogue
	 * @var Catalogue
	 */
	private static $instance;
	private $resources = [];

	/**
	 * Create a new instance - There can only be one Catalogue inststance at any one time, as this will overwrite the
	 * internal instance pointer.
	 */
	public function __construct()
	{
		static::$instance = $this;
	}

	/**
	 * Get the currently active Catalogue. This is a global static instance so that you can call this inside resource
	 * functions to add a new Resource instance.
	 * @return Catalogue
	 */
	public static function getInstance()
	{
		if(static::$instance === null) {
			static::$instance = new self();
		}

		return static::$instance;
	}

	/**
	 * Add a new Resource to the current Catalogue - at this stage it will only enforce that the $name of the resource
	 * is not already in the Catalogue.
	 * @param Resource $resource
	 */
	public function addResource(Resource $resource)
	{
		$name = $resource->getFullName();
		
		if(isset($this->resources[$name])) {
			throw new \Exception("Resource $name is already defined!");
		}

		$this->resources[$name] = $resource;
	}

	/**
	 * Compile the plain list of Resources into an ExecutionPlan that can actually be run. This will check that any
	 * resources required by other resources are present, then build the tree and check for any cyclic dependencies.
	 * 
	 * @return ExecutionPlan
	 */
	public function compile()
	{
		$dependencies = [];

		//Stage 1 - Check that all resources required by any other are present
		foreach($this->resources as $name => $resource) {
			$dependencies[$name] = new Dependency($resource);
			foreach($resource->getRequired() as $required) {
				if(!isset($this->resources[$required])) {
					throw new \Exception("Dependency $required for resource $name could not be found");
				}
			}
		}

		//Stage 2 - Add any required resources as children of the requiring resource
		foreach($dependencies as $name => $dependency) {
			foreach($dependency->getResource()->getRequired() as $required) {
				$dependencies[$required]->addChild($dependency);
			}
		}

		//Stage 3 - Add any resources without parents to the ExecutionPlan - they will be run first, and take all their
		//children with them.
		$plan = new ExecutionPlan();

		foreach($dependencies as $name => $dependency) {
			if(!$dependency->hasParent()) {
				$plan->add($dependency);
			}
		}

		return $plan;
	}
}