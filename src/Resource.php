<?php
namespace Declarative;

class Resource
{
	private $function;
	private $name;
	private $params;

	public function __construct($function, $name, $params)
	{
		$this->function = $function;
		$this->name     = $name;
		$this->params   = $params;
	}

	public function getFullName()
	{
		return ucfirst($function)."['{$this->name}']";
	}

	public function execute()
	{
		return call_user_func_array($this->function, [$params]);
	}
}