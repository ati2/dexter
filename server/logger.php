<?php
//unit tests

//print out errors
function logger($from,$error,$importance){
	$threshold=-1;
	if($importance>$threshold){
		$msg=$importance.'-'.$from.' says '.$error;
		echo "<div id='serverlog'>{$msg}</div>";
	}
	if($importance==1){
		exit("lvl {$importance} error. stopped");
	}
}
?>