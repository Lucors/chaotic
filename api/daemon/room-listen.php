<?php
	// Сессия блокируется при SSE соединении
	// Все остальные входящие запросы, использующие сессию
	// остаются в состоянии pending до конца sse соединения
	if(!isset($_SESSION)){
		// Опция read_and_close позволит избежать этого
		// но теряет актуальность сессии
	    session_start(["read_and_close" => true]);
	}

	header("Cache-Control: no-cache");
	header("Content-Type: text/event-stream\n\n");

    require_once("../log.php");
    require_once("../general.php");
    require_once("../connect.php");
    Log::debug("Запущена sse слушатель комнаты [room-listen.php]"); 
    $response = array();
    $lastData = array();

	if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
		$rid = filter_input(INPUT_GET, "rid");

		if ($rid){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect(); 

		    $connectionOpened = True;
			while ($connectionOpened){
				if (connection_aborted()){
					break;
				}
		   		$response["result"] = False;

			    $result = $link->query("
			        SELECT
			        	rooms.stage as rstage, users.user_id, nick, users.stage as ustage
					FROM
						rooms
					INNER JOIN 
						users
					ON
						rooms.room_id = users.room_id
					WHERE
						rooms.room_id = {$rid}
			    ");

			    // TODO: Придумать как передавать всех пользователей
		   		if ($link->isMysqliResultValid($result)){
			    	$response["users_list"] = array();
			    	// $response["ustgs"] = array();
			    	while ($data = $result->fetch_assoc()) {
			    		$response["stage"] 		  			 = $data["rstage"];
            			$data["avatar_path"] 	  			 = General::getCorrectAvatarPath($data["user_id"]);
            			// $data["friend"] 		  			 = True;
            			// $response["ustgs"][$data["user_id"]] = $data["ustage"];
            			// unset($data["ustage"]);
            			unset($data["rstage"]);
			    		$response["users_list"][] 			 = $data;
			    	}
			    	
		   			$response["result"] = True;
					if ($response["stage"] == RSTAGE_GAME_WAIT){
						unset($response["users_list"]);
						$connectionOpened 	= False;
						$response["result"] = False;
						$response["code"] 	= 2;
					}
		   		}
		   		else {
					$connectionOpened 	= False;
					$response["result"] = False;
		   			$response["code"] 	= 1;
		   		}

		   		// Отправляем данные, если есть изменения
		   		if (!$response["result"] || 
		   			($response["result"] && ($lastData != $response)) ){
		   			$lastData = $response;
					// echo "retry: 1500";
					echo "data: ".json_encode($response)."\n\n";
		   		}
				else {
					echo "data: \n\n";
				}
				// echo "retry: 1500";
				// echo "data: ".json_encode($response)."\n\n";
				ob_end_flush();
  				flush();
				sleep(1.5);
				unset($response["stage"]);
				unset($response["users_list"]);
				unset($response["code"]);
			}

			$link->disconnect();
		}
	}
?>