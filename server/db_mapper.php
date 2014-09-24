<?php
	include('configs.php');
	$db=db_connect();
	$WORDS=mapper_get_all_words($db);
	$LINKS=mapper_get_all_links($db);
	$db->close();
	print_json_format($WORDS,$LINKS);
	
	
	function mapper_get_all_words($db){
		$results=$db->query('SELECT * FROM words');
		$final=array();
		while($row=$results->fetch_assoc()){
			array_push($final,'"'.$row['word'].'":'.$row['total_freq']);
		}
		$final=implode(',',$final);
		return $final;
	}
	function mapper_get_all_links($db){
		$results=$db->query('SELECT * FROM links');
		$final=array();
		while($row=$results->fetch_assoc()){
			array_push($final,'"'.$row['word_from'].'":"'.$row['word_to'].'":'.$row['weight']);
		}
		$final=implode(',',$final);
		return $final;		
	}
	function print_json_format($nodes,$edges){ //i think i'm missing my json package on my local stack. 
		echo '{';
		echo '"nodes":['.format_json_nodes($nodes).'],';
		echo '"edges":['.format_json_edges($edges).']';
		echo '}';
	}
	function format_json_nodes($nodes){
		$output;
		foreach(explode(',',$nodes) as $node){
			if(strlen($output)){
				$output.=',';
			}
			$node=explode(':',$node);
			$output.='{';
			$output.='"id":'.$node[0];
			$output.=',"label":'.$node[0];
			$output.=',"size":'.$node[1];
			$output.='}';
		}
		return $output;
	}
		function format_json_edges($edges){
		$output;
		$i=0;
		foreach(explode(',',$edges) as $edge){
			if(strlen($output)){
				$output.=',';
			}
			$edge=explode(':',$edge);
			$output.='{';
			$output.='"id":"e'.$i.'",';
			$output.='"source":'.$edge[0];
			$output.=',"target":'.$edge[1];
			$output.='}';
			$i++;
		}
		return $output;
	}
?>