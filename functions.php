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
	$itemsArr = [];
	$stop     = false;

	// put multi lines comments in comment container.
	$commentContainer = '';
	// retrieve comment string of alias or functions, except custom tag (#alfred;)
	$clearCommentString = false;

	foreach ($lines as $line) {

		$line = str_replace(array("\r", "\n"), '', $line);

		$oneCmd             = [];
		$cmd                = '';
		$uid                = '';
		$subTitle           = "";
		$valid              = false;
		$arg                = "";
		$canAddItemToResult = false;

		// start with comment tag
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

				if ($key === 'parameters' | $key === 'var') {
					if ($value === 'none') {
						$valid = true;
					}
					$cmd .= " ($value) ";
				}

				if ($key === 'description') {
					$subTitle .= $value;
				}

				if ( ! $withParameter || ($withParameter && $canAddItemToResult)) {
					$oneCmd = alfredItem($cmd, $uid, $arg, $subTitle, $valid);
				}
			}
			$itemsArr           = addAlfredItem($oneCmd, $itemsArr);
			$clearCommentString = true;

		} else if ($aliasCmdArr = getAliasCmd($line, $queryString)) {
			// check if it is alias

			[$commentString] = getCommentLines($commentContainer);
			[$aliasCmd, $content] = $aliasCmdArr;

			$title              = 'Alias: ' . $aliasCmd;
			$subTitle           = trim($commentString, ', ') . $content;
			$valid              = true;
			$arg                = $aliasCmd;
			$oneCmd             = alfredItem($title, $aliasCmd, $arg, $subTitle, $valid);
			$itemsArr           = addAlfredItem($oneCmd, $itemsArr);
			$clearCommentString = true;

		} else if ($funcCmdArr = getFunctionCmds($line, $queryString)) {
			// check if it is functions
			[$commentString, $parameterString] = getCommentLines($commentContainer);
			[$funcCmd, $content] = $funcCmdArr;

			// check with parameter
			if ($withParameter) {
				[$inputCmd, $firstPara] = explode(' ', $queryString);
			} else {
				$inputCmd = $queryString;
			}

			// check if contains parameter string
			if ( ! empty($parameterString)) {
				$valid           = false;
				$parameterString = ' <' . $parameterString . '>';
			}else{
				// if without parameter string, is valid
				$valid = true;
			}

			$arg = $funcCmd;
			if ($inputCmd === $funcCmd) {
				$stop = true;
				if (preg_match("/ *none */i", $parameterString)) {
					$valid = true;
					$arg = $queryString;
				} elseif (isset($firstPara) && ! empty($firstPara)) {
					$valid    = true;
					$arg = $queryString;
				}
			}

			$title              = 'Func: ' . $funcCmd . $parameterString;
			$subTitle           = trim($commentString, ', ') . $content;
			$oneCmd             = alfredItem($title, $funcCmd, $arg, $subTitle, $valid);
			$itemsArr           = addAlfredItem($oneCmd, $itemsArr);
			$clearCommentString = true;
		} elseif ($comment = getComment($line)) {
			$clearCommentString = false;
			$commentContainer   .= $comment . PHP_EOL;
		} elseif (empty(trim($line))) {
			$clearCommentString = true;
		}

		if ($clearCommentString) {
			$commentContainer = '';
		}

		if ($stop) {
			// only show one item
			break;
		}
	}

	return array_values($itemsArr);
}

/**
 * @param string $commentContainer
 *
 * @return array
 */
function getCommentLines(string $commentContainer): array
{
	$parameterString = false;
	$commentString   = '';

	if ( ! empty($commentContainer)) {
		$commentLinesArr = explode(PHP_EOL, $commentContainer);
		$commentString   = '';
		foreach ($commentLinesArr as $commentLine) {
			$regex = "/^ *(parameter|parameters|var): */i";
			preg_match($regex, $commentLine, $matches);
			if ( ! empty($matches)) {
				$parameterString = str_replace($matches[0], '', $commentLine);
			} else {
				$commentString .= $commentLine . ', ';
			}
		}
	}

	return array($commentString, $parameterString);
}

function getComment($line)
{
	$regex = "/^ *#+ */";
	preg_match($regex, $line, $matches);
	if ( ! empty($matches)) {
		return str_replace($matches[0], '', $line);
	}

	return false;
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

	$queryString    = strtolower($queryString);
	$bashProfileCmd = strtolower(trim($bashProfileCmd));

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
 * Get alias cmd
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
		$alias        = str_replace([' ', '='], '', $matchedAlias);
		// replace first one alias
		$alias = preg_replace('/alias/', '', $alias, 1);
		if (alfredQueryMatch($queryString, $alias)) {
			$content    = str_replace(["'", '"', '\t'], '', substr($line, strlen($matchedAlias)));
			$contentArr = explode('#', $content);
			$detail     = trim($contentArr[0] ?? '');
			$comment    = trim($contentArr[1] ?? '');

			return [$alias, ucfirst($comment) . ' |> ' . $detail];
		}
	}

	return false;
}

function getFunctionCmds($line, $queryString)
{

	$matches = [];
	// looking for 'func() or function func()' and not start with _
	$regex = "/^ *(function? +(?!_)[\w\-_\.\~]+|(?!_)[\w\-_\.\~]+) *\( *\) */";
	preg_match($regex, $line, $matches);
	if ( ! empty($matches)) {
		$matchedFunc = $matches[0];
		$func        = str_replace([' ', 'function', '(', ')'], '', $matchedFunc);
		if (alfredQueryMatch($queryString, $func)) {
			$contentArr = explode('#', $line);
			$detail     = trim($contentArr[0] ?? '');
			$comment    = trim($contentArr[1] ?? '');

			return [$func, ucfirst($comment) . ' |> ' . $detail];
		}
	}

	return false;
}