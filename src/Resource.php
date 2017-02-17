<?php
namespace Declarative;

class Resource
{
	private $functionName;
	private $name;
	private $params;

	public function __construct($functionName, $name, $params)
	{
		$this->functionName = $functionName;
		$this->name         = $name;
		$this->params       = $params;
	}

	public function getFullName()
	{
		$functionName = str_replace(["_","-"], " ", $this->functionName);
		$functionName = ucwords($functionName);
		$functionName = str_replace(" ", "", $functionName);
		return "{$functionName}['{$this->name}']";
	}

	public function execute()
	{
		return call_user_func_array($this->functionName, [$this->name, $this->params]);
	}
}