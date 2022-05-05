<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен settings.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False,
    	"msg" => "Ошибка settings.php"
    ); 

    function getAll(){
    	global $response;

    	try {
			if (!isset($_SESSION["email"])){
				throw new Exception("Пользователь не авторизирован");
			}

			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
			$link->connect();

			$result = $link->query("
		        SELECT
		        	allow_animation
				FROM
					users_settings
				WHERE
					user_id = {$_SESSION['user_id']}
		    ");
	   		if (!$link->isMysqliResultValid($result)){
				throw new Exception("Ошибка получения настроек", 1);
	   		}
	   		unset($response["msg"]);
			$response["settings"] = $result->fetch_assoc();
			$response["result"] = True;

			$link->disconnect();
		}
		catch (Exception $e){
			if ($e->getCode() > 0){
				Log::error($e->getMessage()." [{$email}]");
			}
			else {
				Log::warning($e->getMessage()." [{$email}]");
			}
			$response["msg"] = $e->getMessage();
		}
    }
    function set($data){
    	global $response;

    	try {
			if (!isset($_SESSION["email"])){
				throw new Exception("Пользователь не авторизирован");
			}

	    	$data = json_decode($data, True);
			if (is_null($data)){
				throw new Exception("Ошибка декодирования json строки", 1);
			}

			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
			$link->connect();

			$query = "
		        UPDATE
		        	users_settings
				SET
			";
			foreach ($data as $key => $value) {
				$query .= "{$key} = {$value},";
			}
			$query  = substr($query, 0, -1);
			$query .= " WHERE user_id = {$_SESSION['user_id']}";

			$result = $link->query($query);
	   		if (!$link->isQueryResultValid($result)){
				throw new Exception("Ошибка применения настроек");
	   		}
	   		unset($response["msg"]);
			$response["result"] = True;

			$link->disconnect();
		}
		catch (Exception $e){
			if ($e->getCode() > 0){
				Log::error($e->getMessage()." [{$email}]");
			}
			else {
				Log::warning($e->getMessage()." [{$email}]");
			}
			$response["msg"] = $e->getMessage();
		}
    }


    $method = General::getInputMethod();
    $op = filter_input($method, "op");
	// $op = filter_input(INPUT_POST, "op");
	// $method = INPUT_POST;
	// if (!$op){
	// 	$op = filter_input(INPUT_GET, "op");
	// 	$method = INPUT_GET;
	// }

	if ($op){
		switch ($op) {
			case "getall":
				getAll();
				break;
			case "set":
				$data = filter_input($method, "data");
				set($data);
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>
