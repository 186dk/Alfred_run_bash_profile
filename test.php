<?php
include 'functions.php';

$profilePath = __DIR__ . '/test_sample.sh';
//Get bash profile from home path;
$lines = file($profilePath);

# test commands
$tests       = [];

$queryString = 'vg';
$tests[]     = getBashCommands($lines, $queryString);

$queryString = 'ki';
$tests[]     = getBashCommands($lines, $queryString);

$queryString = 'l';
$tests[]     = getBashCommands($lines, $queryString);

$queryString = 'numFiles';
$tests[]     = getBashCommands($lines, $queryString);

$queryString = 'ql ';
$tests[]     = getBashCommands($lines, $queryString);

$queryString = 'ql ~/test.txt';
$tests[]     = getBashCommands($lines, $queryString);

print_r($tests);


# test function getAllAlias
$testAlias = [
	"alias ll='ls -alh'",
	"alias less1='less -FSRXc'  ",
	"   alias less2='less -FSRXc'  ",
	"   alias   less3='less -FSRXc'  ",
	"   alias Less4='less -FSRXc'  ",
	"   alias Less-5='less -FSRXc'  ",
	"   alias Less_6='less -FSRXc'  ",
	"   alias Less.7='less -FSRXc'  ",
	"   invalid less3='less -FSRXc'  ",
	"   aliasless3='less -FSRXc'  ",
	"   alias='less -FSRXc'  ",
];

$testFuncs = [
	"trash () { command mv \"$@\" ~/.Trash ; }     # trash:        Moves a file to the MacOS trash",
	"func(){ } #comment",
	" func() # comment",
	" func(  ) ",
	" function func() ",
	"function    func() ",

];

foreach ($testFuncs as $func) {
//	print_r(getFunctionCmds($func, 'func')).PHP_EOL;
}

foreach ($testAlias as $line) {
//	print_r(getAliasCmd($line, 'l')).PHP_EOL;
}

# test function getAllAlias
$testCmds = [
	"fix_stty",
	"numFiles",
	"cd..",
	"bash_profile_edit",
	"bash-edit"
];

foreach ($testCmds as $cmd) {
//	print_r(splitCamelCaseString($cmd)).PHP_EOL;
}

