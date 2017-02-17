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
}