<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    Log::msg("Запрошен logout.php"); 

    $response = array(
    	"result" => False
    ); 
    
	if (isset($_SESSION["email"])){
		Log::msg("Пользователь \"{$_SESSION["email"]}\" вышел");
		session_unset();
	}
	else {
		Log::warning("Попытка выхода не аутентифиц. пользователя");
	}

	// Не бесполезно, предполагается, что могут быть условия, при которых "result" все таки будет false
	$response["result"] = True;

	header('Content-type: application/json');
	echo json_encode($response);
?>
