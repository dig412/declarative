<?php

use Declarative\Resource;

class ResourceTest extends PHPUnit_Framework_TestCase
{
	public function testGetFullName()
	{
		$r = new Resource("testfile", "file", []);
		$this->assertEquals("File['testfile']", $r->getFullName());

		$r = new Resource("test-line", "line_in_file", []);
		$this->assertEquals("LineInFile['test-line']", $r->getFullName());
	}

	public function testGetRequired()
	{
		$r = new Resource("testfile", "file", []);
		$this->assertEmpty($r->getRequired());
		
		$r = new Resource("testfile", "file", ["require" => "thing"]);
		$this->assertEquals(["thing"], $r->getRequired());
	}

	public function testExecute()
	{
		function test1($name) {
			return $name . 1;
		}
		$r = new Resource("test-", "test1", []);
		$this->assertEquals("test-1", $r->execute());

		function test2($name, $a) {
			return $name . (2 * $a);
		}
		$r = new Resource("test-", "test2", 2);
		$this->assertEquals("test-4", $r->execute());

		function test3($name, array $a) {
			return $name . (3 * $a["arg1"]);
		}
		$r = new Resource("test-", "test3", ["arg1" => 3]);
		$this->assertEquals("test-9", $r->execute());
	}
}