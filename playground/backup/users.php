<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен users.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False
    ); 

    function getFriends(){
    	global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"]) ){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
		        	users.user_id, nick
				FROM
					friends
				INNER JOIN
					users
				ON
					users.user_id = friends.friend_1_id OR users.user_id = friends.friend_2_id
				WHERE
					(friends.friend_1_id =  {$_SESSION['user_id']} OR friends.friend_2_id = {$_SESSION['user_id']}) AND users.user_id != {$_SESSION['user_id']}	
		    ");

	   		if ($link->isMysqliResultValid($result)){

				while ($data = $result->fetch_assoc()){
					$response["list"][$data["user_id"]]["path"] = General::getCorrectAvatarPath($data["user_id"]);
					$response["list"][$data["user_id"]]["nick"] = $data["nick"];

				}
				$response["result"] = True;
	   		}
	   		else {
				$response["msg"] = "Друзья не найдены";
	   		}

			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для запроса друзей");
		}
    }

    function getByQuery($query){
    	global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"]) ){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    if ($query[0] == "#"){
		    	$query = "users.user_id = ".substr($query, 1);
		    }
		    else {
		    	$query = "users.nick LIKE '%{$query}%'";
		    }
		    Log::debug($query);

		    $result = $link->query("
		        SELECT
		        	users.user_id, nick
				FROM
					users
				WHERE
					{$query}
				LIMIT 40
		    ");

	   		if ($link->isMysqliResultValid($result)){
				while ($data = $result->fetch_assoc()){

			   		$result = $link->query("
				        SELECT
				        	friend_1_id, friend_2_id
						FROM
							friends
						WHERE
							friend_1_id = {$data['user_id']} OR friend_2_id = {$data['user_id']}
						LIMIT 40
				    ");

		   			$data["friend"] = 0;
			   		if ($link->isMysqliResultValid($result)){
						while ($friendTest = $result->fetch_assoc()){
							if ($friendTest['friend_1_id'] == $_SESSION["user_id"] || $friendTest['friend_2_id'] == $_SESSION["user_id"]){
		   						$data["friend"] = 1;
							}
						}
					}



					$data["path"] = General::getCorrectAvatarPath($data["user_id"]);
					$response["list"][$data["user_id"]] = $data;
				}
				$response["result"] = True;
	   		}
	   		else {
				$response["msg"] = "Не найдено";
	   		}

			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для запроса пользователей");
		}
    }


	$op = filter_input(INPUT_POST, "op");
	if (!$op){
		$op = filter_input(INPUT_GET, "op");
	}

	if ($op){
		switch ($op) {
			case "getfriends":
				getFriends();
				break;

			case "getbyquery":
				$query = filter_input(INPUT_POST, "query");
				if ($query){
					getByQuery($query);
				}
				else {
					Log::debug("Пустой или невалидный запрос");
					$response["msg"] = "Невалидный запрос";
				}
				break;

			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>