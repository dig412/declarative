<?php

define("CHANGE", 1);
define("NO_CHANGE", 2);
define("ERROR", 3);

/**
 * Manages existence of a file on the current filesystem.
 *
 * @param  string $name Unique name of this resource
 * @param  array $params Variable parameters for the resource:
 * @param  array $ensure Set to absent to make sure the file does not exist, present to make sure it does exist.
 * @param  string $path The absolute path of the file you want to manage.
 * @param  string $contents If not null, the contents of the file will be set to this. template() is useful here.
 * @return CHANGE|NO_CHANGE|ERROR
 */
function ensure_file($name, array $params) {
	return Declarative\Types\FileSystem::ensure_file($name, $params);
}

/**
 * Add or remove lines from a file.
 *
 * @param  string $name Unique name of this resource
 * @param  array $ensure Set to absent to make sure the line does not exist, present to make sure it does exist.
 * @param  string $path The absolute path of the file you want to manage. This must already exist.
 * @param  string $line The line you want to add or remove. If adding this line will be added at the end of the file.
 * @param  string $match If not null, this will be used as a regex to find $line. E.g. $match="var=.*"
 *                       The first line matching $match will be replaced with $line.
 * @return CHANGE|NO_CHANGE|ERROR
 */
function ensure_file_line($name, array $params) {
	return Declarative\Types\FileSystem::ensure_file_line($name, $params);
}

/**
 * Manages existence of a directory on the current filesystem.
 *
 * @param  string $name Unique name of this resource
 * @param  array $ensure Set to absent to make sure the directory does not exist, present to make sure it does exist.
 * @param  string $path The absolute path of the directory you want to manage.
 * @param  string $recursive If true this will create any nested directories in $path
 * @param  string $mode If provided this will set the mode of the file. Provide as an octal - $mode = 0766
 * @return CHANGE|NO_CHANGE|ERROR
 */
function ensure_directory($name, array $params) {
	return Declarative\Types\FileSystem::ensure_directory($name, $params);
}

/**
 * Renders a template from the local filesystem into a String
 *
 * @param  string $templateFile The absolute path of the template you want to render. This should be a PHP file, probably ".phtml"
 * @param  array $variables Variables you want to be available inside the template. Keys should be the name of the variable, values will be values.
 * @return string Rendered content of the template
 */
function template($templateFile, $variables = []) {
	if (! file_exists($templateFile)) {
		throw new \Exception('Could not find the template file: ' . $templateFile);
	}

	extract($variables);
	ob_start();
	include $templateFile;
	$render = ob_get_contents();
	ob_end_clean();

	return $render;
}