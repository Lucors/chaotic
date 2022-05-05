<?php
	if (!isset($_SESSION)){
		session_start();
	}
	$name = filter_input(INPUT_GET, "name");
	$_SESSION["name"] = $name;
?>
	