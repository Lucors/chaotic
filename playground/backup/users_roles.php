<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен users_roles.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False
    ); 

    function get(){
    	global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
		        	role
		        FROM
		            users_roles
		        WHERE
        			user_id = {$_SESSION["user_id"]}
        		LIMIT 1
		    ");

		    $response["role"] = 0;
			$response["result"] = True;
	   		if ($link->isMysqliResultValid($result)){
	   			$data = $result->fetch_assoc();
				$response["role"] = $data["role"];
	   		}

			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для запроса роли пользователя");
		}
    }




	$op = filter_input(INPUT_POST, "op");
	if (!$op){
		$op = filter_input(INPUT_GET, "op");
	}

	if ($op){
		switch ($op) {
			case "get":
				get();
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>