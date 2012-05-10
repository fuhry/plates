<?php

define('INC', dirname(__FILE__) . '/');

require(INC . 'db-class.php');
require(INC . 'controls.php');

/**
 * Failsafe implementation of get_magic_quotes_gpc()
 * @return bool
 */

function my_get_magic_quotes_gpc()
{
	if(function_exists('get_magic_quotes_gpc'))
	{
		return ( get_magic_quotes_gpc() == 1 );
	}
	else
	{
		return ( strtolower(@ini_get('magic_quotes_gpc')) == '1' );
	}
}

/**
 * Recursive stripslashes()
 * @param array
 * @return array
 */

function stripslashes_recurse($arr)
{
	foreach($arr as $k => $xxxx)
	{
		$val =& $arr[$k];
		if(is_string($val))
			$val = stripslashes($val);
		elseif(is_array($val))
			$val = stripslashes_recurse($val);
	}
	return $arr;
}

/**
 * Recursive function to remove all NUL bytes from a string
 * @param array
 * @return array
 */

function strip_nul_chars($arr)
{
	foreach($arr as $k => $xxxx_unused)
	{
		$val =& $arr[$k];
		if(is_string($val))
			$val = str_replace("\000", '', $val);
		elseif(is_array($val))
			$val = strip_nul_chars($val);
	}
	return $arr;
}

/**
 * If magic_quotes_gpc is on, calls stripslashes() on everything in $_GET/$_POST/$_COOKIE. Also strips any NUL characters from incoming requests, as these are typically malicious.
 * @ignore - this doesn't work too well in my tests
 * @todo port version from the PHP manual
 * @return void
 */
function strip_magic_quotes_gpc()
{
	if(my_get_magic_quotes_gpc())
	{
		$_POST    = stripslashes_recurse($_POST);
		$_GET     = stripslashes_recurse($_GET);
		$_COOKIE  = stripslashes_recurse($_COOKIE);
		$_REQUEST = stripslashes_recurse($_REQUEST);
	}
	$_POST    = strip_nul_chars($_POST);
	$_GET     = strip_nul_chars($_GET);
	$_COOKIE  = strip_nul_chars($_COOKIE);
	$_REQUEST = strip_nul_chars($_REQUEST);
}

strip_magic_quotes_gpc();
