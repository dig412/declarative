<?php
namespace Declarative;

/**
 * Defines a Resource - something that can be executed and will return a CHANGE|NO_CHANGE|ERROR value.
 */
interface ExecutableResource
{
	/**
	 * If this resources requires any others to be executed before it is, it should return an array of their names here.
	 * @return array
	 */
	public function getRequired();

	/**
	 * Returns the full name of the resource, usually in the format Type[name]
	 * @return String
	 */
	public function getFullName();

	/**
	 * Triggers the execution of this resource.
	 * @return CHANGE|NO_CHANGE|ERROR
	 */
	public function execute();
}