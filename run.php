<?php

/**
 * @param string $queryString
 * @param bool $withParameter true only show one item
 * @return array
 */
function getCmds(string $queryString, $withParameter = false): array
{
//echo $firstWord;
    $home = $_SERVER['HOME'];
    $profilePath = $home . '/.bash_profile';
    $lines = file($profilePath);

//$resultArr = [
//    'items' => [
//        [
//            'valid' => 'true',
//            'uid' => 'running-cmd-chrome',
//            'title' => 'Open chrome',
//            'subtitle' => 'Please type url without http',
//            'arg' => $queryString
//        ]
//    ]
//];

    $itemsArr = [];
    $stop = false;

    foreach ($lines as $line) {
        if (strpos($line, '#alfred;') === 0) {

            // lower case
            $line = strtolower($line);
            $queryString = strtolower($queryString);

            $bashCommentSegments = explode(';', $line);

            // remove first element, which is #alfred;
            array_shift($bashCommentSegments);

            $oneCmd = [];
            $title = "";
            $uid = "";
            $subTitle = "";
            $valid = false;
            $arg = "";
            $canAddItem = false;

            foreach ($bashCommentSegments as $segment) {
                // replace space
                $segment = trim(preg_replace('/\s\s+/', ' ', $segment));
                list($key, $value) = explode(':', $segment);
                $key = trim($key);
                $value = trim($value);

                if ($key == 'command') {
                    $bashCmd = $value;
                    $uid = $bashCmd;
                    $arg = $bashCmd;

                    list($inputCmd) = explode(' ', $queryString);

                    // this is one with argument
                    if ($withParameter) {
                        $arg = $queryString;
                        $valid = true;
                        if ($inputCmd == $bashCmd) {
                            $canAddItem = true;
                            $stop = true;
                        }
                    } elseif (substr($bashCmd, 0, strlen($queryString)) != $queryString) {
                            // not start with query letter, stop to show
                            break;
                        }


                    $title .= 'Cmd: ' . $bashCmd;
                }

                if ($key == 'parameters') {
                    if ($value == 'none') {
                        $valid = true;
                    }
                    $title .= " (parameter: $value) ";
                }

                if ($key == 'description') {
                    $subTitle .= $value;
                }


                // add custom keys that defined in bash file
//                if (!in_array($key, ['command', 'parameters', 'description'])) {
//                    $oneCmd[$key] = $value;
//                }
                if (!$withParameter || ($withParameter && $canAddItem)) {
                    $oneCmd ['title'] = $title;
                    $oneCmd ['uid'] = $uid;
                    $oneCmd['arg'] = $arg;
                    $oneCmd ['subtitle'] = $subTitle;
                    $oneCmd ['valid'] = $valid ? "true" : "false";
                    $oneCmd ['autocomplete'] = $arg;
                }
            }
            if (!empty($oneCmd)) {
                $itemsArr[] = $oneCmd;
            }
            if ($stop) {
                // only show one item
                break;
            }
        }
    }
    return $itemsArr;
}


// remove first argument
$argv[0] = '';
$queryString = trim($argv[1]);

$searchArr = explode(' ', $queryString);

if (count($searchArr) == 1) {
    $queryString = $searchArr[0];
    $itemsArr = getCmds($queryString);
} else {
    // if with parameter, so only show one item
    $itemsArr = getCmds($queryString, true);

}

$resultArr = [
    'items' => $itemsArr
];
// Loop through our array, show HTML source as HTML source; and line numbers too.


$result = json_encode($resultArr);
echo $result;


