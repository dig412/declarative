<?php
namespace Declarative;

class RootResource implements ExecutableResource
{
	public function getFulllName()
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