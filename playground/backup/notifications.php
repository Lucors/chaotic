<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен notifications.php"); 

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
		        	notification_id, type,
		        	users.nick, users.user_id
				FROM
					notifications
				INNER JOIN
					users
				ON
					notifications.sender_id = users.user_id
				WHERE
					notifications.recipient_id = {$_SESSION['user_id']}
		    ");

	   		if ($link->isMysqliResultValid($result)){
				while ($data = $result->fetch_assoc()){
					$data["path"] = General::getCorrectAvatarPath($data["user_id"]);

					$response["list"][$data["notification_id"]] = $data;

				}
				$response["result"] = True;
	   		}

			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для запроса уведомлений");
		}
    }


    function setNotification($uid, $type, $content = null){
    	global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"]) ){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    if ($type == 1 || $type == 2){
			    $result = $link->query("
			    	SELECT 
			    		sender_id, recipient_id, type
			    	FROM
			    		notifications
			    	WHERE 
			    		(sender_id = {$_SESSION['user_id']} OR recipient_id = {$_SESSION['user_id']}) 
			    		AND
			    		type = 1 OR type = 2
			    ");
			    if ($link->isQueryResultValid($result)){
			    	return;
			    }
		    }

		    if (!$content){
		    	$content = "null";
		    }
		    $result = $link->query("
		    	INSERT INTO
		    		notifications (sender_id, recipient_id, type, content) 
		    	VALUES
		    		({$_SESSION["user_id"]}, {$uid}, 1, {$content})
		    ");

	   		if ($link->isQueryResultValid($result)){
				$response["result"] = True;
	   		}
	   		else {
				$response["msg"] = "Ошибка добавления в друзья";
	   		}


			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для добавления уведомления");
		}
    }


    function answerNotification($nid, $answer){
		global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"]) ){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    try {
		    	$result = $link->query("
			        SELECT
			        	type, sender_id
					FROM
						notifications
					WHERE
						notifications.notification_id = {$nid}
			    ");
			    if (!$link->isMysqliResultValid($result)){
					throw new Exception("Уведомление не найдено");
			    }
			    $data = $result->fetch_assoc();


		    	$result = $link->query("
			        DELETE FROM
						notifications
					WHERE
						notifications.notification_id = {$nid}
			    ");
			    if (!$link->isQueryResultValid($result)){
					throw new Exception("Ошибка ответа на уведомление");
			    }


		    	if ($answer == "accept"){
				    if ($data["type"] == 1){
			    		$result = $link->query("
					        INSERT INTO
					        	friends
					        VALUES 
					        	({$_SESSION['user_id']}, {$data['sender_id']})
					    ");
					    if (!$link->isQueryResultValid($result)){
							throw new Exception("Ошибка добавления в друзья");
				   		}
			    	}
			    	else if ($data["type"] == 2) {
			    		// Тут переброс в комнату нужную
			    	}
		    	}
		    	
		    	$response["result"] = True;

		    }
   			catch (Exception $e){
				Log::error($e->getMessage()." (nid={$nid})");
				$response["msg"] = $e->getMessage();
   			}


			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для добавления уведомления");
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

			case "set":
				$uid = filter_input(INPUT_POST, "uid");
				$type = filter_input(INPUT_POST, "type");
				$content = filter_input(INPUT_POST, "content");

				if ($type && $uid){
					setNotification($uid, $type, $content);
				}
				break;

			case "answer":
				$nid = filter_input(INPUT_POST, "nid");
				$answer = filter_input(INPUT_POST, "answer");

				if ($nid && $answer){
					answerNotification($nid, $answer);
				}
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>