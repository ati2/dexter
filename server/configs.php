<?php
function config_root(){
	return 'C:/winlamp/webfiles/';
}

function db_connect(){
	$user='root';
	$pass='mysql';
	$project='dexter';
	$db=new mysqli("localhost", $user,$pass,$project);
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	return $db;
}
 ?>