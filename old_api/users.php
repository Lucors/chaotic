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
	   		}
	   		else {
				$response["msg"] = "Друзья не найдены";
	   		}
			$response["result"] = True;

			$link->disconnect();
		}
		else {
			Log::warning("Недостаточно прав для запроса пользователей");
			$response["msg"] = "Пользователь не авторизирован";
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

// TODO: КРУТОЙ ЗАПРОС ЛИБО УЗНАТЬ ЧЕ ТАМ С ПЕРЕЗАПИСЬЮ MYSQLI_RESULT
// ГОТОВО!
		    $result = $link->query("
		        SELECT
				    user_id, nick, friend_1_id, friend_2_id
				FROM
				    users
				LEFT JOIN friends ON
					(friend_1_id = {$_SESSION["user_id"]} AND friend_2_id = user_id)
				    OR
					(friend_1_id = user_id AND friend_2_id = {$_SESSION["user_id"]})
				WHERE
				    {$query}
				    AND
				    user_id != {$_SESSION["user_id"]}
				LIMIT 40
		    ");

	   		if ($link->isMysqliResultValid($result)){
				while ($data = $result->fetch_assoc()){
		   			$data["friend"] = 0;
		   			if (!is_null($data["friend_1_id"]) AND !is_null($data["friend_2_id"])){
		   				$data["friend"] = 1;
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
			Log::warning("Недостаточно прав для запроса пользователей");
			$response["msg"] = "Пользователь не авторизирован";
		}
    }

    function getRole(){
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
			Log::warning("Недостаточно прав для запроса роли пользователя");
			$response["msg"] = "Пользователь не авторизирован";
		}
    }


    $method = General::getInputMethod();
    $op = filter_input($method, "op");

	if ($op){
		switch ($op) {
			case "getfriends":
				getFriends();
				break;

			case "getrole":
				getRole();
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