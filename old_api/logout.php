<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен logout.php"); 

    $response = array(
    	"result" => False
    ); 
    
	if (isset($_SESSION["email"])){
		$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		$link->connect();
		$result = $link->query("
			UPDATE
				users
			SET
				last_dt = '".date('Y-m-d H:i:s')."',
				room_id = NULL,
				stage = ".USTAGE_INAUTH."
			WHERE
    			email = '{$_SESSION["email"]}'
	    ");
	    if (!$link->isQueryResultValid($result)){
			Log::warning("Не обнов. поле stage=USTAGE_INAUTH для пользователя \"{$_SESSION["email"]}\"");
	    }
		$link->disconnect();

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
