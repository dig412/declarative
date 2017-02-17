<?php
namespace Declarative;

class Resource implements ExecutableResource
{
	private $functionName;
	private $name;
	private $params;

	public function __construct($name, $functionName, $params)
	{
		$this->name         = $name;
		$this->functionName = $functionName;
		$this->params       = $params;
	}

	public function getRequired()
	{
		if(isset($this->params["require"])) {
			$required = $this->params["require"];
			return is_array($required) ? $required : [$required];
		}
		return [];
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