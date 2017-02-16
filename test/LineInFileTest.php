<?php

class LineInFileTest extends PHPUnit_Framework_TestCase
{
	private $existingFilePath;
	private $nonExistingFilePath;
	private $contents;

	public function setUp()
	{
		$this->filePath = __DIR__ . "/file.txt";

		$contents = [
			"line1",
			"line2",
			"line3=originalvalue",
			"line4 = [testing'quotes']",
		];

		file_put_contents($this->filePath, implode("\r\n", $contents));
	}

	public function tearDown()
	{
		if(file_exists($this->filePath)) {
			unlink($this->filePath);
		}
	}

	public function lineIsInFile($line)
	{
		$contents = file($this->filePath);
		return in_array($line. PHP_EOL, $contents);
	}

	public function testAbsentIsKeptAbsent()
	{
		$result = ensure_file_line("test", [
			"ensure" => "absent",
			"path"   => $this->filePath,
			"line"   => "lineX"
		]);

		$this->assertEquals(NO_CHANGE, $result);
		$this->assertFalse($this->lineIsInFile("lineX"));
	}

	public function testPresentIsMadeAbsent()
	{
		$result = ensure_file_line("test", [
			"ensure" => "absent",
			"path"   => $this->filePath,
			"line"   => "line2"
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertFalse($this->lineIsInFile("line2"));
	}

	public function testAbsentIsMadePresent()
	{
		$result = ensure_file_line("test", [
			"ensure" => "present",
			"path"   => $this->filePath,
			"line"   => "line9"
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertTrue($this->lineIsInFile("line9"));
	}

	public function testPresentIsKeptPresent()
	{
		$result = ensure_file_line("test", [
			"ensure" => "present",
			"path"   => $this->filePath,
			"line"   => "line2"
		]);

		$this->assertEquals(NO_CHANGE, $result);
		$this->assertTrue($this->lineIsInFile("line2"));
	}

	public function testContentsAreChanged()
	{
		$result = ensure_file_line("test", [
			"ensure" => "present",
			"path"   => $this->filePath,
			"line"   => "line3=newvalue",
			"match"  => "line3="
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertTrue($this->lineIsInFile("line3=newvalue"));
		$this->assertFalse($this->lineIsInFile("line3=originalvalue"));
	}

	public function testRegexLineContentsAreChanged()
	{
		$result = ensure_file_line("test", [
			"ensure" => "present",
			"path"   => $this->filePath,
			"line"   => "lineX = [*testing\"quotes'!]]",
			"match"  => "line4 = [testing'quotes'!]"
		]);

		$this->assertEquals(CHANGE, $result);
		$this->assertTrue($this->lineIsInFile("lineX = [*testing\"quotes'!]]"));
		$this->assertFalse($this->lineIsInFile("line4 = [testing'quotes'!]"));
	}
}