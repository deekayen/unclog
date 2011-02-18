#!/usr/local/bin/php -q
<?php

/**
 * This script removes entries from Apache webserver logs
 * coming from worms sitting on unpatched Windows systems with IIS.
 *
 * @author David Norman <david at deekayen [.] net>
 * @copyright 2001 All Rights Reserved. No warranty.
 */

echo "Starting filter...\n";


/**************** CHANGE THESE ****************/
// change this to the location of the log you want to de-worm
#$log_location = "/usr/local/apache/logs/access_log";

// the file you want to create
// if this is an existing file, it will be OVERWRITTEN
#$filtered_log = "/usr/local/apache/logs/new_log";
/**************** END CHANGING ****************/

if(!$log_location || !$filtered_log) {
	echo "Please uncomment the variables for the Apache log location\n";
	exit();
}

// define worm GET requests
$worms = array(	'/winnt/system32/cmd.exe\?/c\+dir', // w32nimdda
		'root.exe\?/c\+dir', // w32nimdda
		'%u9090%u6858%ucbd3%u7801%u9090%u6858%ucbd3%u7801%u9090%u6858%ucbd3%u7801%u9090%u9090%u8190%u00c3%u0003%u8b00%u531b%u53ff%u0078%u0000%u00=a', // Code Red variants
		'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=X', // I don't know what this is, but it was in my logs
		'/default.ida?XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', // this might never get used in below loops
		'/default.ida?NNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNNN'); // same deal on this one, but just in case...


// make sure it doesn't time out on us when we're working
set_time_limit(0);

$fp = fopen($log_location, "r");
$fp2 = fopen($filtered_log, "w");
while(!feof($fp)) {
	$buffer = fgets($fp, 1024);

	$pass = TRUE;
	for($i=0; $i<sizeof($worms); $i++) {
		if($pass) // do check to save on extra processing with ereg
			$pass = (!ereg($worms[$i], $buffer)) ? TRUE : FALSE;
	}
	if($pass) fputs($fp2, $buffer);
}
fclose($fp);
fclose($fp2);

echo "Done!\n";

?>