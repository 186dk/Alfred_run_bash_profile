<?php
include 'functions.php';
// remove first argument
$argv[0]     = '';
$queryString = $argv[1];
$searchArr   = explode(' ', $queryString);
$home        = $_SERVER['HOME'] . '/';

$paths = ['.bash_profile', 'my_bash/profile.sh', '.profile', '.zprofile'];

$lines = [];
foreach ($paths as $path) {
	$fullPath = $home . $path;
	if (file_exists($fullPath)) {
		$fileLines = file($fullPath);
		foreach ($fileLines as $fileLine) {
			$lines[] = $fileLine;
		}
	}
}

//Get bash profile from home path;
if (count($searchArr) === 1) {
	$queryString = $searchArr[0];
	$itemsArr    = getBashCommands($lines, $queryString);
} else {
	// if with parameter, so only show one item
	$itemsArr = getBashCommands($lines, $queryString, true);
}

$resultArr = [
	'items' => $itemsArr
];
// Loop through our array, show HTML source as HTML source; and line numbers too.

$result = json_encode($resultArr, JSON_THROW_ON_ERROR, 512);
echo $result;





