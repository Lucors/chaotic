<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен rooms.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False
    ); 

    function getAll(){
    	global $response;

		try {
			if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"]) ){
    			throw new Exception("Недостаточно прав");
    		}
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
    				rooms.room_id, rooms.name, privacy, maps.name as map_name, COUNT(*) as playes_num
				FROM
				    rooms
				INNER JOIN
					users
				ON
					users.room_id = rooms.room_id
				INNER JOIN
					maps
				ON
					maps.map_id = rooms.map_id
				WHERE
					rooms.stage = 0
				GROUP BY
					room_id, rooms.name, privacy
				LIMIT 40
		    ");

	   		if (!$link->isMysqliResult($result)){
				throw new Exception("Ошибка получения списка комнат");
			}
			while ($data = $result->fetch_assoc()){
				$response["list"][$data["room_id"]] = $data;
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

	function get($id){
    	global $response;

		try {
			if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"])){
    			throw new Exception("Недостаточно прав");
    		}
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
    				rooms.room_id, rooms.name, privacy, rooms.step_time,
    				maps.name as map_name, maps.sprites_path as map_path, 
    				COUNT(*) as playes_num
				FROM
				    rooms
				INNER JOIN
					users
				ON
					users.room_id = rooms.room_id
				INNER JOIN
					maps
				ON
					maps.map_id = rooms.map_id
				WHERE 
					rooms.stage = 0 AND rooms.room_id = {$id}
				GROUP BY
					room_id, rooms.name, privacy
		    ");

	   		if (!$link->isMysqliResultValid($result)){
    			throw new Exception("Ошибка получения данных комнаты");
			}
			$data = $result->fetch_assoc();
			$data["map_preview"] = General::getSpritesPath($data["map_path"])."map-preview.png";
			$response = array_merge($response, $data);
			$response["result"] = True;
			$link->disconnect();
		}
		catch (Exception $e) {
			$response["msg"] = $e->getMessage();	
			if ($e->getCode() == 0){
				Log::warning($e->getMessage()." [get]");
			}
			else {
				Log::error($e->getMessage()." [get]");
			}
		}
    }

    function createRoom($input){
    	global $response;

    	try {
    		if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"]) ){
    			throw new Exception("Недостаточно прав для создания комнаты");
    		}
    		$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		    	SELECT
		    		room_id
		    	FROM 
		    		users
		    	WHERE
		    		user_id = {$_SESSION['user_id']}
			");
			if ($link->isMysqliResultValid($result)){
				$data = $result->fetch_assoc();
				if (!is_null($data["room_id"])){
    				throw new Exception("Вы уже в комнате");
				}
			}

			$result = $link->query("
		    	SELECT
		    		room_id, creator_id
		    	FROM 
		    		rooms
		    	WHERE
		    		creator_id = {$_SESSION['user_id']}
			");
			if ($link->isMysqliResultValid($result)){
				$data = $result->fetch_assoc();
				if (!is_null($data["creator_id"])){
					// исправить поведение выхода из аккаунта
    				forceExitRoom($data["room_id"], $link);
				}
			}

    		// name, step_time, privacy, password, topics[]
    		$input = json_decode($input, True);
    		if (is_null($input)){
    			throw new Exception("Ошибка декодирования json строки");
			}

			if($input["privacy"] == 1 && empty($input["password"])){
    			throw new Exception("Не передан пароль для закрытой комнаты");
			}
			if ($input["step_time"] > 40 || $input["step_time"] < 10){
    			throw new Exception("Неверное значение \"Время ответа\"");
			}

			Log::error("
				INSERT INTO
					rooms (name, privacy, password, step_time, stage, creator_id, map_id)
				VALUES 
					('{$input['name']}', {$input['privacy']}, '{$input['password']}',
						{$input['step_time']}, 0, {$_SESSION['user_id']}, {$input['map_id']})
			");
			$result = $link->query("
				INSERT INTO
					rooms (name, privacy, password, step_time, stage, creator_id, map_id)
				VALUES 
					('{$input['name']}', {$input['privacy']}, '{$input['password']}',
						{$input['step_time']}, 0, {$_SESSION['user_id']}, {$input['map_id']})
			");
			if (is_null($result)){
    			throw new Exception("Ошибка создания комнаты");
			}
			$response["roomID"] = $roomID = $link->mysqli->insert_id;

			$query = "
				INSERT INTO
					rooms_topics (room_id, topic_id)
				VALUES 
			";
			$i = 0;
			foreach ($input["topics"] as $value) {
				if ($i == 4){
					break;
				}

				$query .= "({$roomID}, $value),";
			}
			$query = substr($query, 0, -1);

			$result = $link->query($query);
			if (!$link->isQueryResultValid($result)){
    			throw new Exception("Ошибка определения тем комнаты", $roomID);
			}

			$result = $link->query("
				UPDATE
					users
				SET 
					room_id = {$roomID},
					stage = ".USTAGE_INROOM_READY."
				WHERE
					user_id = {$_SESSION['user_id']}
			");
			if (!$link->isQueryResultValid($result)){
    			throw new Exception("Ошибка добавления игрока в комнату", $roomID);
			}

			unset($response["msg"]);
			$response["result"] = True;
			$link->disconnect();
    	}
    	catch (Exception $e) {
    		if ($e->getCode() == 0){
				Log::warning($e->getMessage()." [createRoom]");
    		}
    		else {
    			$roomID = $e->getCode();
				$link->query("
					DELETE FROM
						rooms
					WHERE
						room_id = {$roomID}
				");
				$link->query("
					DELETE FROM
						rooms_topics
					WHERE
						room_id = {$roomID}
				");
				Log::error($e->getMessage()." [createRoom]");
    		}

			$response["msg"] = $e->getMessage();	
    	}
    }

    //TODO: ИЗМЕНИТЬ МЕХАНИЗМ ВЫХОДА ИЗ КОМНАТЫ
    //Если чел создатель комнаты -- удалить, иначе выход простой
    function forceExitRoom($roomID, $link){
    	global $response;

		$result = $link->query("
	    	UPDATE
	    		users
	    	SET 
	    		room_id = null,
	    		stage = ".USTAGE_INMENU."
	    	WHERE
	    		user_id = {$_SESSION['user_id']}
		");
		if (!$link->isQueryResultValid($result)){
			throw new Exception("Не удалось установить room_id в null", 1);
		}

		$result = $link->query("
			SELECT
				creator_id
			FROM
				rooms
			WHERE
				room_id = {$roomID}
		");
		if (!$link->isMysqliResultValid($result)){
			throw new Exception("Комната не найдена");
		}
		$roomCreatorID = $result->fetch_assoc()["creator_id"];
		if ($roomCreatorID == $_SESSION['user_id']){
			$result = $link->query("
		    	DELETE FROM 
		    		rooms_topics
		    	WHERE
		    		room_id = {$roomID}
			");
			if (!$link->isQueryResultValid($result)){
				Log::warning("Не удалось удалить темы комнаты [exitRoom]");
				$response["msg"] = "Не удалось удалить темы комнаты";
			}

			$result = $link->query("
		    	UPDATE
		    		users
		    	SET 
		    		room_id = null
		    	WHERE
		    		room_id = {$roomID}
			");
			if (!$link->isQueryResultValid($result)){
				Log::warning("Не удалось set room_id = null for users in room [exitRoom]");
				$response["msg"] = "Не удалось выбросить игроков из комнаты";
			}

			$result = $link->query("
		    	DELETE FROM 
		    		rooms
		    	WHERE
		    		room_id = {$roomID}
		    		AND 
		    		creator_id = {$_SESSION['user_id']} 
			");
			if (!$link->isQueryResultValid($result)){
				Log::warning("Не удалось удалить вашу комнату [exitRoom]");
				$response["msg"] = "Не удалось удалить вашу комнату";
			}
		}
    }
    function exitCurrentRoom(){
    	global $response;

    	try {
    		if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"]) ){
    			throw new Exception("Недостаточно прав");
    		}
    		$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		    	SELECT
		    		room_id
		    	FROM 
		    		users
		    	WHERE
		    		user_id = {$_SESSION['user_id']}
			");
			if (!$link->isMysqliResultValid($result)){
    			throw new Exception("Пользователь не в комнате");
			}
			$roomID = $result->fetch_assoc()["room_id"];
			forceExitRoom($roomID, $link);

			unset($response["msg"]);
			$response["result"] = True;
			$link->disconnect();
    	}
    	catch (Exception $e) {
			$response["result"] = True;
			$response["msg"] = $e->getMessage();
    		if ($e->getCode() == 0){
				Log::warning($e->getMessage()." [exitRoom]");
    		}
    		else {
				Log::error($e->getMessage()." [exitRoom]");
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
			case "get":
				$id = filter_input($method, "id");
				if ($id){
					get($id);
				}
				break;
			case "create":
				$data = filter_input($method, "data");
				if ($data){
					createRoom($data);
				}
				break;
			case "exit":
				exitCurrentRoom();
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>