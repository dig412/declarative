<?php

use Declarative\Catalogue;
use Declarative\Resource;

class CatalogueTest extends PHPUnit_Framework_TestCase
{
	public function testGetInstance()
	{
		$c = new Catalogue();
		$c2 = Catalogue::getInstance();
		$this->assertSame($c, $c2);
	}

	public function testSingleResource()
	{
		$c = new Catalogue();
		$r1 = new Resource("test", "test", []);

		$c->addResource($r1);
	}


	public function testDuplicateResource()
	{
		$c = new Catalogue();
		$r1 = new Resource("test", "test", []);

		$c->addResource($r1);
		$this->expectException(Exception::class);
		$c->addResource($r1);
	}

	public function testResourceNameCollision()
	{
		$c = new Catalogue();
		$r1 = new Resource("test", "test", []);
		$r2 = new Resource("test", "test", ["x"]);

		$c->addResource($r1);
		$this->expectException(Exception::class);
		$c->addResource($r2);
	}

	public function testPresentDependency()
	{
		$c = new Catalogue();
		$r1 = new Resource("aaa", "test", []);
		$r2 = new Resource("bbb", "test", ["require" => "Test['aaa']"]);

		$c->addResource($r1);
		$c->addResource($r2);

		$plan = $c->compile();

		$root = $plan->getRoot();
		//Check that the plan has 1 child
		$this->assertEquals(1, count($root->getChildren()));
		//That the first child is R1
		$firstChild = $root->getChildren()[0];
		$this->assertEquals($r1, $firstChild->getResource());
		//And that the first child has one child, R2
		$this->assertEquals(1, count($firstChild->getChildren()));
		$secondChild = $firstChild->getChildren()[0];
		$this->assertEquals($r2, $secondChild->getResource());
		//And that R2 has no children
		$this->assertFalse($secondChild->hasChildren());
	}

	public function testMissingDependency()
	{
		$c = new Catalogue();
		
		$r = new Resource("ccc", "test", ["require" => "Test['xxx']"]);
		$c->addResource($r);
		$this->expectException(Exception::class);
		$c->compile();
	}
}