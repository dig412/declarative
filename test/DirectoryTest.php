<?php

use Declarative\Types\FileSystem;

class DirectoryTest extends PHPUnit_Framework_TestCase
{
	private $existingFilePath;
	private $nonExistingFilePath;
	private $contents;

	function setUp()
	{
		$this->existingFilePath    = __DIR__ . "/exists";
		$this->nonExistingFilePath = __DIR__ . "/nonexistant";

		mkdir($this->existingFilePath);

		if(file_exists($this->nonExistingFilePath)) {
			rmdir($this->nonExistingFilePath);
		}
	}

	function tearDown()
	{
		if(file_exists($this->existingFilePath)) {
			rmdir($this->existingFilePath);
		}
		if(file_exists($this->nonExistingFilePath)) {
			rmdir($this->nonExistingFilePath);
		}
	}

	public function testAbsentIsKeptAbsent()
	{
		$result = FileSystem::ensure_directory("test", [
			"ensure" => "absent",
			"path"   => $this->nonExistingFilePath
		]);

		$this->assertEquals(NO_CHANGE, $result);
		$this->assertTrue(!file_exists($this->nonExistingFilePath));
	}

	public function testPresentIsMadeAbsent()
	{
		$result = FileSystem::ensure_directory("test", [
			"ensure" => "absent",
			"path"   => $this->existingFilePath
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertTrue(!file_exists($this->existingFilePath));
	}

	public function testAbsentIsMadePresent()
	{
		$result = FileSystem::ensure_directory("test", [
			"ensure" => "present",
			"path"   => $this->nonExistingFilePath
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertTrue(file_exists($this->nonExistingFilePath));
		$this->assertTrue(is_dir($this->nonExistingFilePath));
	}

	public function testPresentIsKeptPresent()
	{
		$result = FileSystem::ensure_directory("test", [
			"ensure" => "present",
			"path"   => $this->existingFilePath
		]);

		$this->assertEquals(NO_CHANGE, $result);
		$this->assertTrue(file_exists($this->existingFilePath));
		$this->assertTrue(is_dir($this->existingFilePath));
	}
}