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
    Log::debug("Запущена sse слушатель сообщений [deamon-msg-listen.php]"); 
    $response = array();
    $lastData = array();

	if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
		$uid = filter_input(INPUT_GET, "uid");
		$lastID = filter_input(INPUT_GET, "lastid");

		if ($uid && $lastID){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect(); 

			while (True){
	            if (connection_aborted()){
	                break;
	            }
		   		$response["result"] = False;

			    $result = $link->query("
			        SELECT
			        	*
					FROM
						private_messages
					WHERE
						(
							(sender_id = {$uid} AND recipient_id = {$_SESSION['user_id']})
							OR
							(sender_id = {$_SESSION['user_id']} AND recipient_id = {$uid})
						)
						AND 
						private_message_id > {$lastID}
					ORDER BY
					 	private_message_id
					LIMIT 20
			    ");

		   		if ($link->isMysqliResultValid($result)){
			    	$response["list"] = array();
		   			// $haveUnchecked = False;
					$haveUnchecked = True;
			    	while ($data = $result->fetch_assoc()){
			    		$response["list"][] = $data;
			    		if ($data["checked"] == 0 && $data['recipient_id'] == $_SESSION["user_id"]){
							// $haveUnchecked = True;
						}
			    		$lastID = $data["private_message_id"];
			    	}
			    	$query = "
			    		UPDATE
			    			private_messages
			    		SET checked = 1
			    		WHERE
			    			recipient_id = {$_SESSION['user_id']} 
			    			AND 
			    			sender_id = {$uid}
			    	";

			    	if (end($response["list"])["sender_id"] == $_SESSION["user_id"]){
			    		array_pop($response["list"]);
			    	}


					if ($haveUnchecked){
						$result = $link->query($query);
					    if (!$link->isQueryResultValid($result)){
					    	Log::error("Ошибка обновления параметров сообщений настроек (SSE)");
					    }
					}
		   			$response["result"] = True;
		   		}

					// TODO: РАЗБЕРИСЬ С CHECKED = 1 ДЛЯ ВСЕХ СООБЩЕНИЙ
					// Демон то ли не хочет развывать соединение 
					// и продолжает чекать СООБЩЕНИЯ
		   		// if ($lastData != $response){
		   			$lastData = $response;
		   			echo "data: ".json_encode($response)."\n\n";
		   		// }
				ob_end_flush();
				flush();
				sleep(1.5);
				unset($response["list"]);
			}

			$link->disconnect();
		}
	}
?>