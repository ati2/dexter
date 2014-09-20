<?php
$user='root';
$pass='mysql';
$db='dexter';

function db_connect(){
	return new mysqli("localhost", $user,$pass,$db);
}
 ?>