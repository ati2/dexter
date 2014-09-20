<?php
/*
	analyst.php	requires log.php, database.php. is called by crawler.php
	-reads 1 url
	-cleans text
	-links document to database
*/
require 'logger.php';
require 'database.php';

function analyst_visit(){
	$address=$_GET['address'];
	if(!$address){ return; }
	
	logger('crawler','visiting '.$address,0);
	$raw=file_get_contents($address);
	$startpos=stripos($raw,'>',strpos($raw,'mw-content-text'))+1;
	$endpos=stripos($raw,'<span',(strpos($raw,'id="See_also"')-30));
	$cut=substr($raw,$startpos,$endpos-$startpos);
	//only return the body
	return $cut;
}


?>