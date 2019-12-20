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
	$stop     = false;

	foreach ($lines as $line) {
		$line = str_replace(array("\r", "\n"), '', $line);

		$oneCmd             = [];
		$cmd                = '';
		$uid                = '';
		$subTitle           = "";
		$valid              = false;
		$arg                = "";
		$canAddItemToResult = false;

		if (strpos($line, '#alfred;') === 0) {
			// lower case
			$bashCommentSegments = explode(';', $line);
			// remove first element, which is #alfred;
			array_shift($bashCommentSegments);

			// Comment tag in bash profile
			// #alfred; command:XXX; parameters:XXX, ('none' means no parameter); description: XXX
			foreach ($bashCommentSegments as $segment) {
				// replace space
				$segment = trim(preg_replace('/\s\s+/', ' ', $segment));
				[$key, $value] = explode(':', $segment);
				$key   = trim($key);
				$value = trim($value);

				if ($key === 'command') {
					$bashCmd = $value;
					$uid     = $bashCmd;
					$arg     = $bashCmd;

					[$inputCmd] = explode(' ', $queryString);

					// this is one with argument
					if ($withParameter) {
						$arg   = $queryString;
						$valid = true;

						if ($inputCmd === $bashCmd) {
							$canAddItemToResult = true;
							$stop               = true;
						}
					} elseif ( ! alfredQueryMatch($queryString, $bashCmd)) {
						// not start with query letter, stop to show
						break;
					}

					$cmd .= 'Cmd: ' . $bashCmd;
				}

				if ($key === 'parameters') {
					if ($value === 'none') {
						$valid = true;
					}
					$cmd .= " (parameter: $value) ";
				}

				if ($key === 'description') {
					$subTitle .= $value;
				}

				if ( ! $withParameter || ($withParameter && $canAddItemToResult)) {
					$oneCmd = alfredItem($cmd, $uid, $arg, $subTitle, $valid);
				}
			}
			$itemsArr = addAlfredItem($oneCmd, $itemsArr);

		} else if ($aliasCmd = getAliasCmd($line, $queryString)) {
			// check if it is alias
			[$alias, $content] = $aliasCmd;
			$title    = 'Alias: ' . $alias;
			$valid    = true;
			$arg      = $alias;
			$oneCmd   = alfredItem($title, $alias, $arg, $content, $valid);
			$itemsArr = addAlfredItem($oneCmd, $itemsArr);
		}

		if ($stop) {
			// only show one item
			break;
		}
	}

	return array_values($itemsArr);
}

/**
 * @param string $queryString
 * @param string $bashProfileCmd
 *
 * @return bool
 */
function alfredQueryMatch(string $queryString, string $bashProfileCmd): bool
{
	// allow to show all alfred items at first call
	if (empty($queryString)) {
		return true;
	}

	$queryString = strtolower($queryString);
	$bashProfileCmd = trim($bashProfileCmd);

	if ( ! empty($bashProfileCmd)) {
		// only check first part of query string, the rest is argument
		[$inputCmd] = explode(' ', $queryString);
		// check query if same as bash command
		if ($inputCmd === $bashProfileCmd) {
			return true;
		}

		// show recommendation of bash command if it contain input cmd
		$cmds = array_map('strtolower', splitCamelCaseString($bashProfileCmd));
		foreach ($cmds as $cmd) {
			if (strpos($cmd, $inputCmd) === 0) {
				return true;
			}
		}
	}

	return false;
}

/**
 * @param string $queryString
 *
 * @return array
 */
function splitCamelCaseString(string $queryString): array
{
	//split string to array according to CamelString, connect-by, connect_by
	return preg_split('/(?=[A-Z])|_|-/', $queryString);
}

/**
 * @param array $oneCmd
 * @param array $itemsArr
 *
 * @return array
 */
function addAlfredItem(array $oneCmd, array $itemsArr): array
{
	if ( ! empty($oneCmd) && ! array_key_exists($oneCmd['uid'], $itemsArr)) {
		$itemsArr[$oneCmd['uid']] = $oneCmd;
	}

	return $itemsArr;
}

/**
 * @param string $title
 * @param string $uid
 * @param string $arg
 * @param string $subTitle
 * @param bool   $valid
 *
 * @return array
 */
function alfredItem(string $title, string $uid, string $arg, string $subTitle, bool $valid): array
{
	$oneCmd                  = [];
	$oneCmd ['title']        = $title;
	$oneCmd ['uid']          = $uid;
	$oneCmd ['arg']          = $arg;
	$oneCmd ['subtitle']     = $subTitle;
	$oneCmd ['valid']        = $valid ? "true" : "false";
	$oneCmd ['autocomplete'] = $arg;

	return $oneCmd;
}

/**
 * Get
 *
 * @param $line
 *
 * @param $queryString
 *
 * @return bool|mixed
 */
function getAliasCmd($line, $queryString)
{
	$matches = [];
	// looking for 'alias xx-_xx='
	$regex = "/^ *alias +[\w\-_\.\~]+=/";
	preg_match($regex, $line, $matches);
	if ( ! empty($matches)) {
		$matchedAlias = $matches[0];
		$alias        = str_replace([' ', 'alias', '='], '', $matchedAlias);

		if (alfredQueryMatch($queryString, $alias)) {
			$content    = str_replace(["'", '"', '\t'], '', substr($line, strlen($matchedAlias)));
			$contentArr = explode('#', $content);
			$detail     = trim($contentArr[0] ?? '');
			$comment    = trim($contentArr[1] ?? '');

			return [$alias, $detail . ' #' . $comment];
		}
	}

	return false;
}
