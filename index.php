<?php

define("CHANGE", 1);
define("NO_CHANGE", 2);
define("ERROR", 3);

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

function ensure_file_line($name, array $params) {

	$defaults = [
		"ensure" => null,
		"path"   => null,
		"line"   => null,
		"match"  => null,
	];

	$params = array_merge($defaults, $params);

	// $replace = false;

	if($params["match"] !== null) {
		$regex = "/" . preg_quote($params["match"]) . "/";
		// $replace = true;
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
}