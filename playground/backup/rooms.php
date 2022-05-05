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

		if (isset($_SESSION["email"])){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    $result = $link->query("
		        SELECT
    				rooms.room_id, name, privacy, COUNT(*) as playes_num
				FROM
				    rooms
				INNER JOIN
					users
				ON
					users.room_id = rooms.room_id
				GROUP BY
					room_id, name, privacy
				LIMIT 40
		    ");

	   		if ($link->isMysqliResultValid($result)){
				while ($data = $result->fetch_assoc()){
					$response["list"][$data["room_id"]] = $data;
				}
				$response["result"] = True;
	   		}

			$link->disconnect();
		}
		else {
			Log::warning("Недостаточно прав для запроса роли пользователя");
		}
    }


    function createRoom($input){
    	global $response;

    	try {
    		if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"]) ){
    			throw new Exception("Недостаточно прав для запроса роли пользователя");
    		}
    		$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

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

			$result = $link->query("
				INSERT INTO
					rooms (name, privacy, password, step_time, stage, creator_id)
				VALUES 
					('{$input['name']}', {$input['privacy']}, '{$input['password']}',
						{$input['step_time']}, 0, {$_SESSION['user_id']})
			");
			if (!$link->isQueryResultValid($result)){
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
					room_id = {$roomID}
				WHERE
					user_id = {$_SESSION['user_id']}
			");
			if (!$link->isQueryResultValid($result)){
    			throw new Exception("Ошибка добавления игрока в комнату", $roomID);
			}

			$response["result"] = True;
			unset($response["msg"]);
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

			$response["result"] = False;
			$response["msg"] = $e->getMessage();	
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
			case "create":
				$data = filter_input(INPUT_POST, "data");
				if ($data){
					createRoom($data);
				}
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>