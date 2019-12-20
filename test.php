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

//print_r($tests);


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