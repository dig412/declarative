<?php
namespace Declarative;

/**
 * ExecutionPlan holds the tree of all Dependencies for the current catalogue run.
 * When execute() is called it will call execute on the top level RootResource, and execution will proceed in a depth
 * first manner from there.
 *
 * It accepts a Logger that will be notified of all sucesses and errors in the execution run.
 */
class ExecutionPlan
{
	private $root;
	private $logger;

	public function __construct()
	{
		$this->root = new Dependency(new RootResource());
	}

	/**
	 * Add a new top level Dependency - one that has no parent.
	 * @param Dependency $topLevelDependency
	 */
	public function add(Dependency $topLevelDependency)
	{
		$this->root->addChild($topLevelDependency);
	}

	/**
	 * Returns the Dependency(RootResource) that is the start of the plan. You should only really need this for
	 * debugging and testing - most of the time you will just call execute();
	 * @return Dependency 
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Set the Logger instance that will be used to report on the progress of the execution
	 * @param Logger $logger 
	 */
	public function setLogger(\Psr\Log\LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	public function execute()
	{
		$this->executeResource($this->root);
	}

	/**
	 * Recursive DFS implementation
	 * @param  Dependency $dependency [description]
	 * @return [type]                 [description]
	 */
	private function executeResource(Dependency $dependency)
	{
		$result = $dependency->getResource()->execute();
		$name = $dependency->getResource()->getFullName();
		if($result == CHANGE) {
			$this->logger->info("$name - returned success");
			foreach ($dependency->getChildren() as $child) {
				$this->executeResource($child);
			}
		} else if ($result == ERROR) {
			foreach ($dependency->getChildren() as $child) {
				$name = $child->getResource()->getFullName();
				$this->logger->error("$name - skipping because of failed dependency");
			}
		}
	}
}