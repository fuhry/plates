<?php

global $_dbconn;
function db_connect()
{
	require(INC . 'db-config.php');
	
	global $_dbconn;
	$_dbconn = mysql_connect($db_config['host'], $db_config['user'], $db_config['pass']);
	if ( !$_dbconn )
		die(mysql_error());
	
	if ( !mysql_select_db($db_config['db'], $_dbconn) )
		die(mysql_error());
}

function db_query($q)
{
	global $_dbconn;
	if ( empty($_dbconn) )
		db_connect();
	
	if ( !($result = mysql_query($q, $_dbconn)) )
		die("<h1>SQL error</h1>
				<p>Error: " . htmlspecialchars(mysql_error()) . "</p>
				<p>Query:</p>
				<pre>" . htmlspecialchars($q) . "</pre>");
		
	return $result;
}

function db_fetch($result)
{
	return mysql_fetch_assoc($result);
}
