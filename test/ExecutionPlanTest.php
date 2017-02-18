<?php

use Declarative\ExecutionPlan;
use Declarative\Dependency;
use Declarative\ExecutableResource;

class ExecutionPlanTest extends PHPUnit_Framework_TestCase
{
	private $logger;
	private $plan;

	public function setUp()
	{
		$this->logger = new MockLogger();
		$this->plan = new ExecutionPlan();
		$this->plan->setLogger($this->logger);
	}

	public function testRootExecute()
	{
		$this->plan->execute();
		$this->assertEquals("Root[] - returned success", $this->logger->getLastMessage());
	}

	public function testOneChildExecute()
	{
		$this->plan->add(new Dependency(new MockResource("Test")));
		$this->plan->execute();
		$this->assertEquals("Test - returned success", $this->logger->getLastMessage());
	}

	public function testTwoChildExecute()
	{
		$this->plan->add(new Dependency(new MockResource("Cats")));
		$this->plan->add(new Dependency(new MockResource("Dogs")));
		$this->plan->execute();
		$this->assertEquals([
			"Root[] - returned success",
			"Cats - returned success",
			"Dogs - returned success"
		], $this->logger->getMessages());
	}

	public function testOneDependencyExecute()
	{
		$d1 = new Dependency(new MockResource("Cats"));
		$d2 = new Dependency(new MockResource("Mice"));
		$d1->addChild($d2);
		$this->plan->add($d1);
		$this->plan->execute();
		$this->assertEquals([
			"Root[] - returned success",
			"Cats - returned success",
			"Mice - returned success"
		], $this->logger->getMessages());
	}

	public function testThreeChildOneDependency()
	{
		$d1 = new Dependency(new MockResource("Horses"));
		$d2 = new Dependency(new MockResource("Cats"));
		$c1 = new Dependency(new MockResource("Mice"));
		$d2->addChild($c1);
		$d3 = new Dependency(new MockResource("Dogs"));
		$this->plan->add($d1);
		$this->plan->add($d2);
		$this->plan->add($d3);
		$this->plan->execute();

		//Strategy is DFS, so we should go Horses , Cats + children, Dogs

		$this->assertEquals([
			"Root[] - returned success",
			"Horses - returned success",
			"Cats - returned success",
			"Mice - returned success",
			"Dogs - returned success"
		], $this->logger->getMessages());
	}

	public function testError()
	{
		$this->plan->add(new Dependency(new MockResource("Test", ERROR)));
		$this->plan->execute();
		$this->assertEquals([
			"Root[] - returned success",
			"Test - failed with error",
		], $this->logger->getMessages());
	}

	public function testSkipChildren()
	{
		$d1 = new Dependency(new MockResource("Cats", ERROR));
		$d2 = new Dependency(new MockResource("Mice"));
		$d1->addChild($d2);
		$d3 = new Dependency(new MockResource("Dogs"));
		$this->plan->add($d1);
		$this->plan->add($d3);
		$this->plan->execute();
		$this->assertEquals([
			"Root[] - returned success",
			"Cats - failed with error",
			"Mice - skipping because of failed dependency",
			"Dogs - returned success",
		], $this->logger->getMessages());
	}
}

class MockLogger extends \Psr\Log\AbstractLogger
{
	private $messages = [];
	
	public function log($level, $message, array $context = [])
	{
		$this->messages[] = $message;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function getLastMessage()
	{
		return end($this->messages);
	}
}


class MockResource implements ExecutableResource
{
	private $name;
	private $return;

	public function __construct($name, $return = CHANGE)
	{
		$this->name   = $name;
		$this->return = $return;
	}

	public function getRequired()
	{
		return [];
	}

	public function getFullName()
	{
		return $this->name;
	}

	public function execute()
	{
		return $this->return;
	}
}