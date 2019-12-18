<?php
############ Help func #################
/**
 * @param array  $lines
 * @param string $queryString
 * @param bool   $withParameter true only show one item
 *
 * @return array
 */
function getBashCommands(array $lines, string $queryString, $withParameter = false): array
{
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
		$line = strtolower($line);
		$queryString = strtolower($queryString);
		if (strpos($line, '#alfred;') === 0) {
			// lower case
			$bashCommentSegments = explode(';', $line);
			// remove first element, which is #alfred;
			array_shift($bashCommentSegments);

			$oneCmd = [];
			$title = '';
			$uid = '';
			$subTitle = "";
			$valid = false;
			$arg = "";
			$canAddItem = false;

			// Comment tag in bash profile
			// #alfred; command:XXX; parameters:XXX, ('none' means no parameter); description: XXX
			foreach ($bashCommentSegments as $segment) {
				// replace space
				$segment = trim(preg_replace('/\s\s+/', ' ', $segment));
				list($key, $value) = explode(':', $segment);
				$key = trim($key);
				$value = trim($value);

				if ($key === 'command') {
					$bashCmd = $value;
					$uid = $bashCmd;
					$arg = $bashCmd;

					list($inputCmd) = explode(' ', $queryString);

					// this is one with argument
					if ($withParameter) {
						$arg = $queryString;
						$valid = true;
						if ($inputCmd === $bashCmd) {
							$canAddItem = true;
							$stop = true;
						}
					} elseif (!empty($queryString) && strpos($bashCmd, $queryString) !== 0) {
						// not start with query letter, stop to show
						break;
					}

					$title .= 'Cmd: ' . $bashCmd;
				}

				if ($key === 'parameters') {
					if ($value === 'none') {
						$valid = true;
					}
					$title .= " (parameter: $value) ";
				}

				if ($key === 'description') {
					$subTitle .= $value;
				}

				if (!$withParameter || ($withParameter && $canAddItem)) {
					$oneCmd ['title'] = $title;
					$oneCmd ['uid'] = $uid;
					$oneCmd ['arg'] = $arg;
					$oneCmd ['subtitle'] = $subTitle;
					$oneCmd ['valid'] = $valid ? "true" : "false";
					$oneCmd ['autocomplete'] = $arg;
				}
			}
			if (!empty($oneCmd) && !array_key_exists($title, $itemsArr)) {
				$itemsArr[$title] = $oneCmd;
			}
			if ($stop) {
				// only show one item
				break;
			}
		}
	}
	return array_values($itemsArr);
}

function getAllAlias($line){
	$matches = [];
	$regex = "/ .alias =(\W*)/";
	preg_match_all($regex, $line, $matches);
	return $matches;
}
