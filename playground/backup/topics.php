<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен topics.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False
    ); 

    function getAll(){
    	global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"]) ){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
		        	topic_id, name, icon_path
				FROM
					topics
		    ");

	   		if ($link->isMysqliResultValid($result)){
	   			$response["topics"] = array();
				while ($data = $result->fetch_assoc()){
					$topic = array();
					$topic[] = $data["topic_id"];
					$topic[] = $data["name"];
					$topic[] = General::getCorrectTopicIcoPath($data["icon_path"]);

					$response["topics"][] = $topic;

				}
				$response["result"] = True;
	   		}

			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для запроса уведомлений");
		}
    }



	$op = filter_input(INPUT_POST, "op");
	if (!$op){
		$op = filter_input(INPUT_GET, "op");
	}

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