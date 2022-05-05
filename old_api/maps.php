<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен maps.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False,
    	"msg" => "Ошибка получения карт"
    ); 

    function getAll(){
    	global $response;

		if (isset($_SESSION["email"])){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
		        	map_id, name
				FROM
					maps
		    ");

		    // response = [ topics = [[topic_id, name, icon_path]] ]
	   		if ($link->isMysqliResultValid($result)){
	   			$response["maps"] = array();
				while ($data = $result->fetch_assoc()){
					$map = array();
					$map[] = $data["map_id"];
					$map[] = $data["name"];

					$response["maps"][] = $map;
				}
				$response["result"] = True;
	   		}

			$link->disconnect();
		}
		else {
			Log::warning("Недостаточно прав для запроса карт");
			$response["msg"] = "Недостаточно прав для запроса карт";
		}
    }



	$method = General::getInputMethod();
    $op = filter_input($method, "op");

	if ($op){
		switch ($op) {
			case "getall":
				getAll();
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>