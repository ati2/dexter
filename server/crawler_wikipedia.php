<?php
include('logger.php');
$debug_mode=($_GET['s']);
$search_params=get_vars_from_input($debug_mode);
$target=crawler_make_validated_link_from($search_params);
$sublinks=crawler_list_related_links_from($search_params,$target);

if($search_params['mode']=='interface'){
	echo $target.';';
	echo implode(',',$sublinks);
}





/* *************************************************************** *\
	get_vars_from_input(url)
		
	based on url, determines if source is debug/interface/robot. 
	inits params with defaults accordingly
\* *************************************************************** */
function get_vars_from_input($debug_mode){
	$params=array();
	$params['s']=($debug_mode)?$_GET['s']:$_POST['s'];
	$params['max']=($debug_mode)?10:$_POST['max'];
	$params['depth']=($debug_mode)?1:$_POST['depth'];
	$params['debug']=($debug_mode)?1:$_POST['debug'];
	$params['type']=($debug_mode)?'wikipedia':$_POST['type'];
	$params['mode']=($debug_mode)?'debug':'interface';
	return $params;
}
/* *************************************************************** *\
	crawler_list_related_links_from(url)
		crawler_cut_body_from_data(raw text)
		crawler_cut_url_from_link(text with '<a>')
		crawler_cut_keyword_from_link(text with '>text</a>')
		
	from a page, return address to articles on this page
\* *************************************************************** */
function crawler_list_related_links_from($search_params,$url){
	$raw=file_get_contents($url);	
	$raw=crawler_cut_body_from_data($raw);
	
	$link_words=array();
	foreach(explode('<a ',$raw) as $keyword){
		if($search_params['max']>0&&$search_params['max']<count($link_words)){break;} //if specified, dont exceed max
		$url=crawler_cut_url_from_link($keyword); 
		if($search_params['type']=='wikipedia'&&strpos($url,'wiki')===false){continue;} //if wiki type,url must be wiki
		$keyword=crawler_cut_keyword_from_link($keyword);
		if(strpos($keyword,'<')!==false){continue;}	//if link not an html tag like img/span
		if(strlen($keyword)){array_push($link_words,'http://en.wikipedia.org'.$url);}
	}
	return $link_words;
}
	function crawler_cut_body_from_data($raw){
		$startpos=strpos($raw,'<a')+2;
		$endpos=(strpos($raw,'id="See_also"'))?strpos($raw,'id="See_also'):strpos($raw,'id="References"');
		$length=$endpos-$startpos;
		return substr($raw,$startpos,$length);
	}
	function crawler_cut_url_from_link($raw){
		$startpos=strpos($raw,'href="')+6;
		$length=strpos($raw,'"',$startpos)-$startpos;
		return substr($raw,$startpos,$length);	
	}
	function crawler_cut_keyword_from_link($raw){
		$startpos=strpos($raw,'>')+1;
		$length=strpos($raw,'</a',$startpos)-$startpos;
		return substr($raw,$startpos,$length);		
	}
/* *************************************************************** *\
	crawler_make_validated_link_from(search_params)
		crawler_read_wikipedia_search(url)
		crawler_get_search_url_from_topic(topic)
		crawler_clean_search_string(topic)
	
	from user input topic, return a valid wikipedia article link
\* *************************************************************** */
function crawler_make_validated_link_from($search_params){
	$search_url=crawler_get_search_url_from_topic($search_params['s']);
	$url=crawler_read_wikipedia_search($search_url);
	return $url;
}
	function crawler_read_wikipedia_search($url){
		$raw=file_get_contents($url);
		$raw=explode('mw-search-result-heading',$raw);
		if(count($raw)<1){ logger('crawler_wikipedia','no search matches',1);}
		
		//grab first search result
		$startpos=strpos($raw[1],'href="')+6;
		$length=strpos($raw[1],'"',$startpos)-$startpos;
		$searchresult=substr($raw[1],$startpos,$length);
		
		return 'http://en.wikipedia.org'.$searchresult;
	} 
	function crawler_get_search_url_from_topic($topic){
		$topic=crawler_clean_search_string($topic);
		if(strlen($topic)<1){logger('crawler_wikipedia','invalid subject',1);}
		return 'http://en.wikipedia.org/w/?title=Special%3ASearch&profile=default&fulltext=Search&search='.$topic;
	}
		function crawler_clean_search_string($dirty_topic){
			//remove_special_chars
			$dirty_topic=preg_replace('/[^a-z A-Z]/','',$dirty_topic);
			$dirty_topic=trim($dirty_topic);
			$topic=str_replace(' ','+',$dirty_topic);
			return $topic;
		}
		
?>