<?php
	// Сессия блокируется при SSE соединении
	// Все остальные входящие запросы, использующие сессию
	// остаются в состоянии pending до конца sse соединения
	if(!isset($_SESSION)){
		// Опция read_and_close позволит избежать этого
		// но теряет актуальность сессии
	    session_start(["read_and_close" => true]);
	}

	header('Cache-Control: no-cache');
	header("Content-Type: text/event-stream\n\n");

    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запущена sse слушатель сообщений [deamon-msg-listen.php]"); 
    $response = array();


	if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
		$uid = filter_input(INPUT_GET, "uid");
		$lastID = filter_input(INPUT_GET, "lastid");

		if ($uid && $lastID){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect(); 

			while (True){
		   		$response["result"] = False;

			    $result = $link->query("
			        SELECT
			        	*
					FROM
						private_messages
					WHERE
						(sender_id = {$uid} OR recipient_id = {$uid})
						AND
						(sender_id = {$_SESSION["user_id"]} OR recipient_id = {$_SESSION["user_id"]})
						AND 
						private_message_id > {$lastID}
					 ORDER BY
					 	private_message_id
			    ");

		   		if ($link->isMysqliResultValid($result)){
		   			$canQuery = false;
			    	$query = "UPDATE private_messages SET `checked` = CASE";
			    	$response["list"] = array();
			    	while ($data = $result->fetch_assoc()) {
			    		$response["list"][] = $data;
			    		if ($data['recipient_id'] == $_SESSION["user_id"]){
			    			$query .= "
				    			WHEN 
				    				sender_id = {$data['sender_id']} AND recipient_id = {$data['recipient_id']}
								THEN 1
							";
							$canQuery = True;
			    		}
			    		$lastID = $data["private_message_id"];
			    	}

			    	if (end($response["list"])["sender_id"] == $_SESSION["user_id"]){
			    		array_pop($response["list"]);
			    	}

					$query .= "ELSE `checked` END";

					if ($canQuery){
						$result = $link->query($query);
					    if (!$link->isQueryResultValid($result)){
					    	Log::error("Ошибка обновления параметров сообщений настроек (SSE)");
					    }
					}
		   			$response["result"] = True;
		   		}


				echo "data: ".json_encode($response)."\n\n";

				ob_end_flush();
				flush();
				sleep(1);
				unset($response["list"]);
			}

			$link->disconnect();
		}
	}
?>