<?php
	if(!isset($_SESSION)){
	    session_start(["read_and_close" => true]);
	}
	header("Cache-Control: no-cache");
	header("Content-Type: text/event-stream\n\n");

    require_once("../log.php");
    require_once("../general.php");
    require_once("../connect.php");
    Log::debug("Запущена sse слушатель игровой сессии [game-listen.php]"); 


	// ROOM SELECTOR DATA
	$roomData = array(
		"creator_id" 	=> 		0,
		"map_scheme" 	=> 		null
	);
	$mapScheme 		=& $roomData["map_scheme"];	// Схема карты (json-->php_array)
	$clientID 		=& $_SESSION["user_id"];	// Ссылка на user_id

	// STEP SELECTOR DATA
	$stepData = array(
		"counter"		=>		-1				// Поле счетчика (now - start_dt) 
	)

	// POSITION SELECTOR DATA
	$positionData = array(
	)

    $response 		= array();					// Переменная ответа
	$lastResponse 	= array();					// Последний ответ сервера
	$canListen 		= true;						// Флаг цикла
	$link 			= null; 					// Соедиение с БД


    require_once("game-listen-methods.php");


	// Точка входа ------------------------------------------------------------------------------------
	// Проверка авторизации игрока
	if (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"])){
		Log::die("Попытка запуска game-listen без авторизации"); 
	}
	$roomData["room_id"] = filter_input(INPUT_GET, "room_id"); // Ожидаем ID комнаты
	// Не передан обязательный параметр 
	if (!isset($roomData["room_id"])){
		Log::die("Попытка запуска game-listen без параметра room_id"); 
	}

	$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
	if (!$link->connect()){
		Log::die("Нет соединения с БД"); 
	} 

	// Получение данных комнаты
	$result = $link->query("
		SELECT
			rooms.name, rooms.creator_id, rooms.stage
			rooms.step_time, rooms.step_max,
			maps.sprites_path
		FROM
			maps
		INNER JOIN 
			rooms
		ON
			rooms.map_id = maps.map_id
		WHERE
			rooms.room_id = {$rid}
	");
	if (!$link->isMysqliResultValid($result)){
		Log::die("Ошибка получения данных комнаты"); 
	}
	$roomData = array_merge($roomData, $result->fetch_assoc());

	$roomData["sprites_path"] = General::getSpritesPath($roomData["sprites_path"]);
	$roomData["map_filename"] = __DIR__."/../../".$roomData["sprites_path"]."scheme.json";
	if (!file_exists($roomData["map_filename"])){
		Log::die("Не найден файл игровой карты");
	}
	$roomData["map_scheme"] = json_decode(file_get_contents($roomData["map_filename"]), True);

	// $output = array("code"=>GCODE_ERROR_PHP, "msg"=>$roomData["map_filename"]);
	// if (!$canListen){
	// 	echo "data: ".json_encode($output)."\n\n";
	// 	ob_end_flush();
	// 	flush();
	// }
	
	// $lastPusher = array();
	// $lastSelector = array();
	// $lastUserList = array();
	while ($canListen && !connection_aborted()){
		// // Ответы в виде списка объектов с кодами
		// $response["list"] = array();



		$tmpGameState = gameState($rid);
		if ($tmpGameState["ulist"] == $lastUserList){
			unset($tmpGameState["ulist"]);
		}
		else {
			$lastUserList = $tmpGameState["ulist"];
		}
		$response["list"][] = $tmpGameState;

		if ($response["list"][0]["code"] == GCODE_SYNC_STAGE){
			$response["list"][] = pingSelector($rid);

			if ($response["list"][1]["code"] == GCODE_PING_TIMEOUT 
				|| $response["list"][1]["code"] == GCODE_PING_WRONG){
				
				$tmp = orderPusher($rid);
				if ($tmp != $lastPusher){
					$lastPusher = $tmp; 
					$response["list"][] = $tmp;
				}
			}
			else {
				if ($response["list"][1]["code"] == GCODE_PING_WAIT){
					if ($response["list"][1]["limit"] == true){
						$response["list"][1]["code"] = GCODE_PING_TIMEOUT;
					}
					$tmp = orderSelector($rid);
					if ($tmp != $lastSelector){
						$lastSelector = $tmp; 
						$response["list"][] = $tmp;
					}
				}
				else {
					// GCODE_PING_CORRECT HANDLER HERE
				}
			}
		}


		// Отправляем данные, если есть изменения
		// if ($lastData != $response){
		// 	$lastData = $response;
			// echo "data: ".json_encode($response)."\n\n";
		// }
		// else {
		// 	echo "data: \n\n";
		// }

		echo "data: ".json_encode($response)."\n\n";
		ob_end_flush();
		flush();
		sleep(1);

		if (isset()){
			unset($response["room"]);
		}
		// unset($response["list"]);
		// unset($response["ulist"]);
		// unset($response["code"]);
	}

	$link->disconnect();
?>