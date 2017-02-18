<?php
namespace Declarative;

class RootResource implements ExecutableResource
{
	public function getFullName()
	{
		return "Root[]";
	}

	public function getRequired()
	{
		return [];
	}

	public function execute()
	{
		return CHANGE;
	}
}