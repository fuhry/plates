<?php

/**
 * I hate dumping shit like this into the codebase, but Cohoe really didn't leave me a choice.
 * Sends e-mail via SMTP.
 * @param string Source address
 * @param string Destination address
 * @param string Subject
 * @param string Message body
 * @param string Additional headers
 * @return bool
 */

function smtp_mail($from, $to, $subject, $body, $headers = '')
{
	list($domain) = array_reverse(explode('@', $to));
	
	// look up MX record for $domain
	$record = dns_get_record($domain, DNS_MX);
	
	if ( !isset($record[0]['target']) )
		// failed to get target server
		return false;
	
	// open socket
	$sock = fsockopen($record[0]['target'], 25, $errno, $errstr, 5);
	if ( !$sock )
		// failed to open socket
		return false;
	
	try
	{
		// wait for 220
		if ( _smtp_get_response($sock) !== 220 )
			throw new Exception("Expected 220");
		
		// HELO
		fputs($sock, "HELO " . gethostname() . "\r\n");
		if ( _smtp_get_response($sock) !== 250 )
			throw new Exception("Expected 250");
		
		// from
		fputs($sock, "MAIL FROM: <$from>\r\n");
		if ( _smtp_get_response($sock) !== 250 )
			throw new Exception("Expected 250");
		
		// to
		fputs($sock, "RCPT TO: <$from>\r\n");
		if ( _smtp_get_response($sock) !== 250 )
			throw new Exception("Expected 250");
		
		// data
		fputs($sock, "DATA\r\n");
		if ( _smtp_get_response($sock) !== 250 )
			throw new Exception("Expected 250");
		
		// send headers
		$full_headers = "Subject: $subject\r\n";
		if ( !empty($headers) )
			$full_headers .= trim(str_replace("\n", "\r\n", str_replace("\r\n", "\n", $headers))) . "\r\n";
		
		$full_headers .= "\r\n";
		fputs($sock, $full_headers);
		
		// send body
		$body = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body));
		fputs($sock, $body);
		
		// send end marker
		fputs($sock, "\r\n.\r\n");
		if ( _smtp_get_response($sock) !== 250 )
			throw new Exception("Expected 250");
		
		// end session
		fputs($sock, "QUIT\r\n");
		if ( _smtp_get_response($sock) !== 221 )
			throw new Exception("Expected 221");
		
		fclose($sock);
	} catch ( Exception $e )
	{
		fputs($sock, "QUIT\r\n");
		_smtp_get_response($sock);
		fclose($sock);
		return false;
	}
	return true;
}

function _smtp_get_response($sock)
{
	while ( !feof($sock) && ($line = fgets($sock, 8192)) )
	{
		if ( preg_match('/^([0-9]+)(\s.+)?$/', $line, $match) )
			return intval($match[1]);
	}
	return false;
}

// smtp_mail('plates@csh.rit.edu', 'plates@csh.rit.edu', 'Test e-mail', 'Testing', 'From: Plates <plates@csh.rit.edu>');	
