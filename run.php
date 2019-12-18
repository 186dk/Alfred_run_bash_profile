<?php
include 'functions.php';
// remove first argument
$argv[0] = '';
$queryString = trim($argv[1]);
$searchArr = explode(' ', $queryString);
$home = $_SERVER['HOME'];
$profilePath = $home . '/.bash_profile';
//Get bash profile from home path;
$lines = file($profilePath);

if (count($searchArr) === 1) {
    $queryString = $searchArr[0];
    $itemsArr = getBashCommands($lines, $queryString);
} else {
    // if with parameter, so only show one item
    $itemsArr = getBashCommands($lines, $queryString, true);
}

$resultArr = [
    'items' => $itemsArr
];
// Loop through our array, show HTML source as HTML source; and line numbers too.

$result = json_encode($resultArr);
echo $result;





