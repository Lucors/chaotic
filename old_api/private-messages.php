<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен private-messages.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False
    ); 


    function getAll($uid){
		global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"]) ){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    try {
		    	$result = $link->query("
			        SELECT
			        	*
					FROM
						private_messages
					WHERE
						(sender_id = {$uid} OR recipient_id = {$uid})
						AND
						(sender_id = {$_SESSION["user_id"]} OR recipient_id = {$_SESSION["user_id"]})
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
			    	}
					$query .= "ELSE `checked` END";

					if ($canQuery){
						$result = $link->query($query);
					    if (!$link->isQueryResultValid($result)){
					    	throw new Exception("Ошибка обновления параметров сообщений настроек");
					    }
					}
			    }

		    	$response["result"] = True;

		    }
   			catch (Exception $e){
				Log::error($e->getMessage());
				$response["msg"] = $e->getMessage();
   			}


			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для получения сообщений");
		}
    }


	function set($uid, $content){
		global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"]) ){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();


		    try {
		    	$result = $link->query("
			        INSERT INTO
			        	private_messages (sender_id, recipient_id, content)
			        VALUES 
			        	({$_SESSION['user_id']}, {$uid}, '{$content}')
			    ");

			    if (!$link->isQueryResultValid($result)){
			    	throw new Exception("Ошибка при добавлении сообщения в БД");
			    }

		    	$response["private_message_id"] = $link->mysqli->insert_id;
	    		$response["result"] = True;
		    }
   			catch (Exception $e){
				Log::error($e->getMessage());
				$response["msg"] = $e->getMessage();
   			}


			$link->disconnect();
		}
		else {
			Log::warn("Недостаточно прав для получения сообщений");
		}
    }


    $method = General::getInputMethod();
    $op = filter_input($method, "op");

	if ($op){
		switch ($op) {
			case "getall":
				$uid = filter_input($method, "uid");
				if ($uid){
					getAll($uid);
				}
				else {
					Log::warning("Запрощены сообщения с неправильным польз. id ({$uid})");
					$response["msg"] = "Неправильный пользовательский id ({$uid})";
				}
				break;

			case "set":
				$uid = filter_input($method, "uid");
				$content = filter_input($method, "content");
				if ($uid && $content){
					set($uid, $content);
				}
				break;

			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>