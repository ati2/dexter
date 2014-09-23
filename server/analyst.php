<?php
/*
	analyst.php	requires log.php, configs.php. is called by crawler.php
	-reads 1 url
	-cleans text
	-links document to database
*/
require 'logger.php';
require 'configs.php';

function init(){
	//processing calls
	$article=analyst_visit();
	$article['body']=analyst_clean_text($article['body']);
	$article['keywords']=list_links_as_important($article['raw']);

	//database calls
	$db=db_connect();
	db_check_for_linkages($db,format_list_for_db_search($article['keywords']));
	db_log_title_into_db($db,$article['title']);
	$db->close();
}init();



function analyst_visit(){
	$address=$_GET['address'];
	if(!$address){ return; }
	
	logger('analyst','visiting '.$address,0);
	$raw=file_get_contents($address);
	$starttitle=strpos($raw,'>',stripos($raw,'dir="auto"'))+1;
	$endtitle=strpos($raw,'</span',$starttitle);
	$title=strtolower(substr($raw,$starttitle,$endtitle-$starttitle));
	
	
	$startbody=stripos($raw,'>',strpos($raw,'mw-content-text'))+1;
	$endbody=stripos($raw,'<span',(strpos($raw,'id="See_also"')-30));
	$body=substr($raw,$startbody,$endbody-$startbody);
	//only return the body
	return array('title'=>$title,'body'=>$body,'raw'=>$body);
}
function analyst_clean_text($body){
	$body=strip_tags($body);
	$body=strtolower($body);
	//ignoring context until the whole system is working
	$body=dumb_strip_all_chars($body);
	$body=dumb_strip_useless_words($body);
	return $body;
}

	function list_links_as_important($raw){
		$important_keywords=array();
		foreach(explode('<a ',$raw) as $keyword){
			$startpos=strpos($keyword,'>')+1;
			$endpos=strpos($keyword,'</a',$startpos);
			
			if(!$endpos||$startpos>=$endpos){continue;}
			$keyword=substr($keyword,$startpos,$endpos-$startpos);
			if(strpos($keyword,'<img')!==false){continue;}
			$keyword=analyst_clean_text($keyword);
			
			if(strlen($keyword)){array_push($important_keywords,$keyword);}
		}
		return $important_keywords;

	}
		function format_list_for_db_search($list){
			return implode(',',array_unique($list));
		}
		function format_list_for_weight($list){
			$counts=array_count_values($list);
			arsort($counts);
			$counts=http_build_query($counts,'','|');
			return $counts;		
		}
	function dumb_strip_useless_words($contextual_body){
		$blacklisted_words=array('the','a','and','at','by','as','edit');
		$important_word_list=array();
		foreach(explode(' ',$contextual_body) as $word){
			if(strlen($word)<2){ continue; }
			if(in_array($word,$blacklisted_words)){ continue; }
			array_push($important_word_list,$word);
		}
		return implode(' ',$important_word_list);
	}
	function dumb_strip_all_chars($dirty_body){
		$stripped_body=preg_replace('/[^a-z]/',' ',$dirty_body);
		return $stripped_body;
	}
	
	
	function db_check_for_linkages($db,$keywords_as_string){
		$keywords_as_string=$db->real_escape_string($keywords_as_string);
		$keywords_as_string=str_replace(' ','+',$keywords_as_string);
		$keywords_as_string="'".str_replace(",","','",$keywords_as_string)."'";
		$querystring="SELECT word FROM words WHERE word in ({$keywords_as_string});";
		
		$results=$db->query($querystring);
		echo '<b>matching db words</b><br>'."\n";
		while($row=$results->fetch_assoc()){
			echo $row['word']."<br>"."\n";
		}
/*		
		if ($stmt = $db->prepare($querystring)) {
			$stmt->execute();
			$stmt->bind_result($queryresult);
			$stmt->fetch();
			echo $queryresult;
			$stmt->close();
		}
*/
	}
	function db_log_title_into_db($db,$title){

	}
?>