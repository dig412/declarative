<?php

use Declarative\Resource;

class ResourceTest extends PHPUnit_Framework_TestCase
{
	public function testGetFullName()
	{
		$r = new Resource("file", "testfile", []);
		$this->assertEquals("File['testfile']", $r->getFullName());

		$r = new Resource("line_in_file", "test-line", []);
		$this->assertEquals("LineInFile['test-line']", $r->getFullName());
	}

	public function testExecute()
	{
		function test1($name) {
			return $name . 1;
		}
		$r = new Resource("test1", "test-", []);
		$this->assertEquals("test-1", $r->execute());

		function test2($name, $a) {
			return $name . (2 * $a);
		}
		$r = new Resource("test2", "test-", 2);
		$this->assertEquals("test-4", $r->execute());

		function test3($name, array $a) {
			return $name . (3 * $a["arg1"]);
		}
		$r = new Resource("test3", "test-", ["arg1" => 3]);
		$this->assertEquals("test-9", $r->execute());
	}
}