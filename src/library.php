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

	$defaults = [
		"ensure"   => null,
		"path"     => null,
		"contents" => null,
	];

	$params = array_merge($defaults, $params);
	
	$fileExists = file_exists($params["path"]);

	if(!$fileExists && $params["ensure"] === "absent") {
		return NO_CHANGE;
	}

	if($fileExists && $params["ensure"] === "absent") {
		unlink($params["path"]);
		return CHANGE;
	}

	$result = NO_CHANGE;

	if(!$fileExists && $params["ensure"] === "present") {
		touch($params["path"]);
		$result = CHANGE;
		$fileExists = true;
	}

	if($fileExists && $params["ensure"] === "present") {
		if($params["contents"] !== null) {
			$currentContents = file_get_contents($params["path"]);

			if($currentContents != $params["contents"]) {
				file_put_contents($params["path"], $params["contents"]);
				$result = CHANGE;
			}
		}
	}

	return $result;
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

	$defaults = [
		"ensure" => null,
		"path"   => null,
		"line"   => null,
		"match"  => null,
	];

	$params = array_merge($defaults, $params);


	if($params["match"] !== null) {
		$regex = "/" . preg_quote($params["match"]) . "/";
	} else {
		$regex = "/^" . preg_quote($params["line"]) . "/";
	}

	if(!file_exists($params["path"])) {
		return ERROR;
	}

	$contents          = file($params["path"]);
	$matchInFile       = false;
	$matchedLineNumber = null;
	foreach($contents as $lineNumber => $line) {
		$matchInFile = preg_match($regex, $line);
		if($matchInFile === 1) {
			$matchedLineNumber = $lineNumber;
			break;
		}
	}

	//Line isn't there, and doesn't need to be
	if(!$matchInFile && $params["ensure"] === "absent") {
		return NO_CHANGE;
	}

	//The line isn't there, but we want it to be - add it alongside some line endings
	if(!$matchInFile && $params["ensure"] === "present") {
		file_put_contents($params["path"], PHP_EOL . $params["line"]. PHP_EOL, FILE_APPEND);
		return CHANGE;
	}

	//Line is there, but shouldn't be - remove it
	if($matchInFile && $params["ensure"] === "absent") {
		unset($contents[$matchedLineNumber]);
		$newContents = implode('', $contents);
		file_put_contents($params["path"], $newContents);
		return CHANGE;
	}

	//Line is there, and we want it to be
	if($matchInFile && $params["ensure"] === "present") {

		$currentLine = rtrim($contents[$matchedLineNumber]);

		if($currentLine == $params["line"]) {
			//$line is already in the file
			return NO_CHANGE;
		} else {
			//$line isn't there, so we want to replace whatever $match found
			array_splice($contents, $matchedLineNumber, 1, $params["line"] . PHP_EOL);
			$newContents = implode('', $contents);
			file_put_contents($params["path"], $newContents);
			return CHANGE;
		}
	}
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

	$defaults = [
		"ensure"    => null,
		"path"      => null,
		"recursive" => false,
		"mode"      => null,
	];

	$params = array_merge($defaults, $params);
	
	$directoryExists = is_dir($params["path"]);

	if(!$directoryExists && $params["ensure"] === "absent") {
		return NO_CHANGE;
	}

	if($directoryExists && $params["ensure"] === "absent") {
		rmdir($params["path"]);
		return CHANGE;
	}

	if(!$directoryExists && $params["ensure"] === "present") {
		mkdir($params["path"], $params["mode"], $params["recursive"]);
		return CHANGE;
	}

	if($directoryExists && $params["ensure"] === "present") {
		return NO_CHANGE;
	}

	return ERROR;
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