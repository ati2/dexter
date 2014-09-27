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
	$params=get_vars_from_input($_GET['address']);
	$article=analyst_visit($params);
	$article['body']=analyst_clean_text($article['body']);
	$article['keywords']=list_links_as_important($article['raw']);

	//database calls
	$db=db_connect();
	$article['links']=db_check_for_linkages($db,format_list_for_db_search($article['keywords']));
	$article['title']=db_log_title_into_db($db,$article['title']);
	db_log_linkages($db,$article);
	
	//double check your work
	db_clean_linkages($db);
	
	$db->close();
}init();


/* *************************************************************** *\
	get_vars_from_input(url)
		
	based on url, determines if source is debug/interface/robot. 
	inits params with defaults accordingly
\* *************************************************************** */
function get_vars_from_input($debug_mode){
	$params=array();
	$params['address']=($debug_mode)?$_GET['address']:$_POST['address'];
	$params['max']=($debug_mode)?10:$_POST['max'];
	$params['depth']=($debug_mode)?1:$_POST['depth'];
	$params['debug']=($debug_mode)?1:$_POST['debug'];
	$params['type']=($debug_mode)?'wikipedia':$_POST['type'];
	$params['mode']=($debug_mode)?'debug':'interface';
	return $params;
}


function analyst_visit($params){
	if(!$params['address']){ return; }
	
	logger('analyst','visiting '.$params['address'],0);
	$raw=file_get_contents($params['address']);
	if(!$raw){ exit();}
	$starttitle=strpos($raw,'>',stripos($raw,'dir="auto"'))+1;
	$endtitle=strpos($raw,'</span',$starttitle);
	$title=strtolower(substr($raw,$starttitle,$endtitle-$starttitle));
	
	$startbody=stripos($raw,'>',strpos($raw,'mw-content-text'))+1;
	$endbody=(strpos($raw,'id="See_also"'))?strpos($raw,'id="See_also'):strpos($raw,'id="References"');
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
			$keyword=str_replace(' ','+',$keyword);
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
		
		$matches=array();
		$results=$db->query($querystring);
		while($row=$results->fetch_assoc()){
			array_push($matches,$row['word']);
		}
		return $matches;
	}
	function db_log_linkages($db,$article){
		if(!$article['title']){ logger('analyst','no title for linkage',1);}
		if(!count($article['links'])){ logger('analyst','no matches for linkage',1);}
		$weights=format_list_for_weight($article['keywords']);
		$sqlvalue=array();
		foreach($article['links'] as $link){
			array_push($sqlvalue,"('{$article['title']}','{$link}',{$weights[$link]})");
		}
		logger('analyst','found '.count($sqlvalue).' links',0);
		$sqlvalue=implode(',',$sqlvalue);
		$querystring="INSERT INTO links (word_from,word_to,weight) VALUES {$sqlvalue} ON DUPLICATE KEY UPDATE weight=VALUES(weight)";
		$db->query($querystring);
		if(mysqli_error($db)){
			logger('analyst',mysqli_error($db),1);
		}else{
			logger('analyst','affected '.$db->affected_rows.' rows',0);
		}
	}
	function db_log_title_into_db($db,$title){
		$title=db_clean_title($db,$title);
		logger('analyst','logging '.$title.' into db',0);
		$querystring="INSERT into words (word) values('{$title}')";
		$db->query($querystring);
		if(mysqli_error($db)){
			logger('analyst',mysqli_error($db),0);
		}
		return $title;
	}
		function db_clean_title($db,$title){
			$title=$db->real_escape_string($title);
			$title=str_replace(' ','+',$title);
			return $title;
		}
	function db_clean_linkages($db){
	
	}
		function db_get_one_sided_links($db){
			$sql='SELECT * 
				FROM (SELECT word_from AS a , word_to AS b FROM `links`) AS reversed 
				LEFT JOIN links  
				ON reversed.b=links.word_from AND reversed.a=links.word_to 
				WHERE  ISNULL(weight)';
		}
		function db_clean_one_sided_links($db){
			$sql='INSERT INTO `links` (word_from,word_to,weight)
				SELECT b,a,0 
				FROM (SELECT word_from AS a , word_to AS b FROM `links`) AS reversed 
				LEFT JOIN links  
				ON reversed.b=links.word_from AND reversed.a=links.word_to 
				WHERE  ISNULL(weight)';
		}
?>