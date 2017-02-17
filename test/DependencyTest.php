<?php

use Declarative\Resource;
use Declarative\Dependency;

class DependencyTest extends PHPUnit_Framework_TestCase
{
	public function testGetResource()
	{
		$r = new Resource("a", "b", []);
		$d = new Dependency($r);
		$this->assertSame($r, $d->getResource());
	}

	public function testNoChildren()
	{
		$r1 = new Resource("a", "b", []);
		$d1 = new Dependency($r1);
		$this->assertFalse($d1->hasChildren());
		$this->assertEmpty($d1->getChildren());
	}

	public function testOneChild()
	{
		$r1 = new Resource("a", "b", []);
		$d1 = new Dependency($r1);
		$r2 = new Resource("c", "d", []);
		$d2 = new Dependency($r2);
		$d1->addChild($d2);
		$this->assertTrue($d1->hasChildren());
		$this->assertNotEmpty($d1->getChildren());
		$this->assertFalse($d2->hasChildren());
		$this->assertEmpty($d2->getChildren());

		$this->assertSame($d2, $d1->getChildren()[0]);
	}

	public function testMultipleChildren()
	{
		$r1 = new Resource("a", "b", []);
		$d1 = new Dependency($r1);
		$this->assertFalse($d1->hasChildren());
		$this->assertEmpty($d1->getChildren());

		$r2 = new Resource("c", "d", []);
		$d2 = new Dependency($r2);
		$d1->addChild($d2);
		$this->assertTrue($d1->hasChildren());
		$this->assertNotEmpty($d1->getChildren());

		$r3 = new Resource("e", "f", []);
		$d3 = new Dependency($r3);
		$d1->addChild($d3);
		$this->assertTrue($d1->hasChildren());
		$this->assertNotEmpty($d1->getChildren());
		$this->assertSame($d3, $d1->getChildren()[1]);
	}

	public function testParent()
	{
		$r1 = new Resource("pp", "11", []);
		$d1 = new Dependency($r1);
		$r2 = new Resource("cc", "22", []);
		$d2 = new Dependency($r2);
		$d1->addChild($d2);
		$this->assertFalse($d1->hasParent());
		$this->assertTrue($d1->hasChildren());
		$this->assertTrue($d2->hasParent());
		$this->assertFalse($d2->hasChildren());
		$this->assertSame($d1, $d2->getParent());
	}

}