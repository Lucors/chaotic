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
    	"result" => False,
		"msg" => "Ошибка получения тем вопросов"
    ); 

    function getAll(){
    	global $response;

		try {
			if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"]) ){
    			throw new Exception("Недостаточно прав для создания комнаты");
    		}
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
		        	topic_id, name, icon_path
				FROM
					topics
		    ");

		    // response = [ topics = [[topic_id, name, icon_path]] ]
			if (!$link->isMysqliResultValid($result)){
    			throw new Exception("Ошибка получения тем вопросов");
	   		}
			$response["topics"] = array();
			while ($data = $result->fetch_assoc()){
				$topic = array();
				$topic[] = $data["topic_id"];
				$topic[] = $data["name"];
				$topic[] = General::getCorrectTopicIcoPath($data["icon_path"]);

				$response["topics"][] = $topic;
			}
			unset($response["msg"]);
			$response["result"] = True;

			$link->disconnect();
		}
		catch (Exception $e) {
			$response["msg"] = $e->getMessage();	

			if ($e->getCode() == 0){
				Log::warning($e->getMessage()." [getAll]");
			}
			else {
				Log::error($e->getMessage()." [getAll]");
			}
		}
    }
	function getRoom($rid){
    	global $response;

		$response["result"] = True; //Независимо от результата
		try {
			if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"]) ){
    			throw new Exception("Недостаточно прав для создания комнаты");
    		}
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
				SELECT
					topics.topic_id, topics.name, topics.icon_path
				FROM
					rooms_topics
				INNER JOIN
					topics
				ON
					topics.topic_id = rooms_topics.topic_id
				WHERE 
					rooms_topics.room_id = {$rid}
		    ");

		    // response = [ topics = [[topic_id, name, icon_path]] ]
	   		if (!$link->isMysqliResultValid($result)){
    			throw new Exception("Ошибка получения тем комнаты");
	   		}
			$response["topics"] = array();
			while ($data = $result->fetch_assoc()){
				$topic = array();
				$topic[] = $data["topic_id"];
				$topic[] = $data["name"];
				$topic[] = General::getCorrectTopicIcoPath($data["icon_path"]);

				$response["topics"][] = $topic;
			}
			unset($response["msg"]);

			$link->disconnect();
		}
		catch (Exception $e) {
			$response["msg"] = $e->getMessage();	

			if ($e->getCode() == 0){
				Log::warning($e->getMessage()." [getRoom]");
			}
			else {
				Log::error($e->getMessage()." [getRoom]");
			}
		}
    }



    $method = General::getInputMethod();
    $op = filter_input($method, "op");

	if ($op){
		switch ($op) {
			case "getall":
				getAll();
				break;
			case "getRoom":
				$rid = filter_input($method, "rid");
				if ($rid){
					getRoom($rid);
				}
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>