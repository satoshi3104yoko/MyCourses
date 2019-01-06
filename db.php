<?php

function db_connect(){
  $dsn = "mysql:dbname=heroku_56c9b7b8d293439;host=us-cdbr-iron-east-01.cleardb.net;charset=utf8";
  $user = "b523bdd9fc94ad";
  $password = "17a81545";

	try{
		$pdo = new PDO($dsn, $user, $password);
		return $pdo;
	}catch (PDOException $e){
	    	// echo('Error:'.$e->getMessage());
	    	die();
	}
}

?>
