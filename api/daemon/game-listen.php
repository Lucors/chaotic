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


	// Переменные
	{
		$sleepTime = 1;

		// ROOM SELECTOR DATA
		$roomData = array(
			"creator_id" 	=> 		0,
			"map_scheme" 	=> 		null			// Схема карты (php_array)
		);
		// $mapScheme 		=& $roomData["map_scheme"];
		$clientID 		=& $_SESSION["user_id"];	// Ссылка на user_id

		// STEP SELECTOR DATA
		$stepData = array(
			"ping"				=>		-1,			// Поле счетчика (now - start_dt) 
			"current_user_id"	=>		null		// ID Текущего игрока
		);

		// POSITION SELECTOR DATA
		$positionData = array(
		);

		$canListen 		= true;						// Флаг цикла
		$link 			= null; 					// Соедиение с БД
		$response 		= array();					// Переменная ответа
		// Последний ответ сервера
		$lastResponse 	= array(
			// "room" 			=>		null,
			"step" 			=>		null,
			"position" 		=>		null
		);					
	}


	// Точка входа
	{
		//Методы запросов к БД (Напрямую, без API)
		require_once("game-listen-methods.php");
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
				rooms.room_id, rooms.name, rooms.creator_id,
				rooms.stage, rooms.step_time, rooms.step_max,
				maps.sprites_path
			FROM
				maps
			INNER JOIN 
				rooms
			ON
				rooms.map_id = maps.map_id
			WHERE
				rooms.room_id = {$roomData['room_id']}
		");
		if (!$link->isMysqliResultValid($result)){
			Log::die("Ошибка получения данных комнаты"); 
		}
		$roomData = array_merge($roomData, $result->fetch_assoc());

		// Получение игровой карты (PHP_ARRAY)
		// $roomData["sprites_path"] = General::getSpritesPath($roomData["sprites_path"]);
		// $roomData["map_filename"] = __DIR__."/../../".$roomData["sprites_path"]."map-scheme.php";
		// require_once($roomData["map_filename"]);
		// $roomData["map_scheme"]	  =& $mapScheme;
		// if (!file_exists($roomData["map_filename"])){
		// 	Log::die("Не найден файл игровой карты");
		// }
		// $roomData["map_scheme"] = json_decode(file_get_contents($roomData["map_filename"]), True);
		
		// Пока флаг $canListen и есть соединение с клиентом
		while ($canListen && !connection_aborted()){
			$sleepTime = 1;

			$response["room"] = roomSelector();
			// Всегда отвечаем состояние комнаты
			// applyResponseChanges("room");


			if ($response["room"]["code"] == SYNC_ROOM_FINISH){
				$response["position"] = positionSelector(true);
			}
			elseif ($response["room"]["code"] >= SYNC_ROOM_OK){
				$response["step"] = stepSelector();
				applyResponseChanges("step");
				
				if ($lastResponse["step"]["code"] == (SYNC_STEP_STAGE + SSTAGE_MOVE_END)){
					$response["position"] = positionSelector();
					applyResponseChanges("position");
				}
			}

			// Отправляем данные
			echo "data: ".json_encode($response)."\n\n";

			// Очищаем буфер
			ob_end_flush();
			flush();
			// Очищаем ответ
			unset($response["room"]);
			unset($response["step"]);
			unset($response["position"]);

			// Часто обновления 1 сек.
			usleep($sleepTime * 1000000);
		}

		$link->disconnect();
	}
?>