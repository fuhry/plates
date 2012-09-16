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

function db_numrows($result)
{
	return mysql_num_rows($result);
}

function db_escape($str)
{
	global $_dbconn;
	if ( empty($_dbconn) )
		db_connect();
	
	return mysql_real_escape_string($str);
}

function db_free_result($result)
{
	return mysql_free_result($result);
}

function db_insert($table, $columns, $rows)
{
	if ( !isset($rows[0]) || !is_array($rows[0]) )
	{
		// single-row insert
		$rows = array($rows);
	}
	asort($columns);
	// for each row, sanitize data
	foreach ( $rows as $i => &$row )
	{
		$colcount = 0;
		foreach ( $row as $k => &$v )
		{
			if ( !is_string($v) && !is_array($v) && !is_numeric($v) )
				die("Only strings, numerics and arrays can be inserted with db_insert()");
			
			if ( !in_array($k, $columns) )
				die("Cannot insert column \"" . htmlspecialchars($k) . "\" at row $i - it was not specified in the column list");
			
			if ( is_array($v) )
				$v = json_encode($v);
			
			if ( is_string($v) )
				$v = "'" . mysql_real_escape_string($v) . "'";
			else if ( is_numeric($v) )
				$v = strval($v);
			
			$colcount++;
		}
		if ( $colcount !== count($columns) )
			die("Row $i is missing columns");
		unset($v);
		ksort($row);
		$row = implode(', ', $row);
	}
	unset($row);
	$rows = '(' . implode("),\n  (", $rows) . ')';
	$sql = sprintf("INSERT INTO %s (%s) VALUES\n  %s", $table, implode(', ', $columns), $rows);
	// echo '<pre>' . htmlspecialchars($sql) . '</pre>';
	db_query($sql);
	return mysql_insert_id();
}
