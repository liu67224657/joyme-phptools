<?php
$com = substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.') + 1);

if ($com == 'beta' || $com == 'com' ) {
	$GLOBALS['config']['db']['db_host'] = 'alyweb005.prod';
	$GLOBALS['config']['db']['db_port'] = 3306;
	$GLOBALS['config']['db']['db_user'] = 'wikiuser';
	$GLOBALS['config']['db']['db_password'] = '123456';
	$GLOBALS['config']['db']['db_name'] = 'phptools';
}else{
	$GLOBALS['config']['db']['db_host'] = '172.16.75.32';
	$GLOBALS['config']['db']['db_port'] = 3306;
	$GLOBALS['config']['db']['db_user'] = 'root';
	$GLOBALS['config']['db']['db_password'] = '123456';
	$GLOBALS['config']['db']['db_name'] = 'phptools';
}

