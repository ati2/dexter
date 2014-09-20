<?php
/*
	crawler.php	requires log.php
	-while testing, reads pages in a directory
	-in final, will crawl pages
	-pass urls to analyst.php
*/
require 'logger.php';
crawler_get_urls('../docs/testfiles');





function crawler_get_urls($dir){
	//directory is just for testing. will make a self growing model. 
	$testdocs=scandir($dir);
	logger('crawler','found '.count($testdocs).' items',0);
	foreach($testdocs AS $doc){
		$doc='../docs/testfiles/'.$doc;
		if(!crawler_validate_url($doc)){ continue; }
		crawler_analyze_page($doc);
	}
}

function crawler_validate_url($address){
	logger('crawler','validating url',0);
	//ONLY used for testing pages captured in dir
	return !is_dir($address);
	//lol. yup no validation atm.
}

function crawler_analyze_page($address){
	//will change this to an async that doesnt wait for response. 
	file_get_contents('analyst.php?address='.$address); 
}
?>