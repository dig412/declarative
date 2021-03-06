<?php

use Declarative\Types\FileSystem;

class FileTest extends PHPUnit_Framework_TestCase
{
	private $templatePath;
	private $existingFilePath;
	private $nonExistingFilePath;
	private $contents;

	function setUp()
	{
		$this->templatePath        = __DIR__ . "/template.phtml";
		$this->existingFilePath    = __DIR__ . "/exists.txt";
		$this->nonExistingFilePath = __DIR__ . "/nonexistant.txt";
		$this->contents = mt_rand();

		touch($this->existingFilePath);

		if(file_exists($this->nonExistingFilePath)) {
			unlink($this->nonExistingFilePath);
		}
	}

	function tearDown()
	{
		if(file_exists($this->existingFilePath)) {
			unlink($this->existingFilePath);
		}
		if(file_exists($this->nonExistingFilePath)) {
			unlink($this->nonExistingFilePath);
		}
	}

	public function testAbsentIsKeptAbsent()
	{
		$result = FileSystem::ensure_file("test", [
			"ensure" => "absent",
			"path"   => $this->nonExistingFilePath
		]);

		$this->assertEquals(NO_CHANGE, $result);
		$this->assertTrue(!file_exists($this->nonExistingFilePath));
	}

	public function testPresentIsMadeAbsent()
	{
		$result = FileSystem::ensure_file("test", [
			"ensure" => "absent",
			"path"   => $this->existingFilePath
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertTrue(!file_exists($this->existingFilePath));
	}

	public function testAbsentIsMadePresent()
	{
		$result = FileSystem::ensure_file("test", [
			"ensure" => "present",
			"path"   => $this->nonExistingFilePath
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertTrue(file_exists($this->nonExistingFilePath));
	}

	public function testPresentIsKeptPresent()
	{
		$result = FileSystem::ensure_file("test", [
			"ensure" => "present",
			"path"   => $this->existingFilePath
		]);

		$this->assertEquals(NO_CHANGE, $result);
		$this->assertTrue(file_exists($this->existingFilePath));
	}

	public function testContentsAreChanged()
	{
		$result = FileSystem::ensure_file("test", [
			"ensure"   => "present",
			"path"     => $this->existingFilePath,
			"contents" => $this->contents
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertEquals($this->contents, file_get_contents($this->existingFilePath));
	}

	public function testContentsAreNotChanged()
	{
		file_put_contents($this->existingFilePath, $this->contents);

		$result = FileSystem::ensure_file("test", [
			"ensure"   => "present",
			"path"     => $this->existingFilePath,
			"contents" => $this->contents
		]);

		$this->assertEquals(NO_CHANGE, $result);
		$this->assertEquals($this->contents, file_get_contents($this->existingFilePath));
	}

	public function testContentsSetFromTemplate()
	{
		$result = FileSystem::ensure_file("test", [
			"ensure"   => "present",
			"path"     => $this->nonExistingFilePath,
			"contents" => template($this->templatePath, ["variable" => "hello"])
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertEquals("variable=hello", file_get_contents($this->nonExistingFilePath));
	}
}