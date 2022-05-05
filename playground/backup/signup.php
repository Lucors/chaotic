<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("general.php");
    require_once("log.php");
    require_once("connect.php");
    Log::msg("Запрошен signup.php"); 

 	$response = array(
    	"result" => False,
		"msg" => "Неверные значения эл. почты или пароля"
    ); 

 	// TODO: Это все в функцию бы. А также: logout.php, login.php
	if (!isset($_SESSION["email"])){
		$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
		$pass = filter_input(INPUT_POST, "pass");
		$nick = filter_input(INPUT_POST, "nick");

		if ($email && $pass && $nick){
			$passhash = password_hash($pass, PASSWORD_DEFAULT);

		    $link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    // TODO: Аналогично организовать: logout.php, login.php
   			try {
				if ($nick[0] == "#"){
					throw new Exception("Недопустимый символ \"#\" в никнейме");
				}


			    // Добавление пользователя
			    $result = $link->query("
			        INSERT INTO
			        	users (email, passhash, nick)
			        VALUES 
			        	('{$email}', '{$passhash}', '{$nick}')
			    ");
			    if (!$link->isQueryResultValid($result)){
					throw new Exception("Ошибка регистрации");
			    }
	   			$userID = $link->mysqli->insert_id;

			//УСТАНОВКА НАСТРОЕК ПО УМОЛЧ. ДЛЯ ПОЛЬЗОВАТЕЛЯ 
				// Запрос настроек  
	   			$result = $link->query("
			        SELECT 
			        	setting_type_id, default_value
			        FROM 
			        	settings_types
			    ");
			    if (!$link->isMysqliResultValid($result)){
					throw new Exception("Ошибка получения настроек", 1);
			    }


			    $query = "
			    	INSERT INTO
			    		settings_values (user_id, setting_type_id, value)
			    	VALUES
			    ";
			    while ($data = $result->fetch_assoc()){
			    	$query .= " ({$userID}, {$data['setting_type_id']}, '{$data['default_value']}')";
			    }
			    // Первоначальная настройка
   				$result = $link->query($query);
			    if (!$link->isQueryResultValid($result)){
					throw new Exception("Ошибка первоначальной настройки", 1);
			    }


			    $_SESSION["email"] = $response["email"] = $email;
				$_SESSION["nick"] = $response["nick"] = $nick;
				$_SESSION["user_id"] = $response["user_id"] = $userID;
				$_SESSION["role"] = $response["role"] = 0;
				$response["result"] = True;
				unset($response["msg"]);

				Log::msg("Зарегистрирован пользователь \"{$nick}:{$email}\"");
				
				$result = $link->query("
					UPDATE
						users
					SET
						last_dt = '".date('Y-m-d H:i:s')."'
					WHERE
	        			email = '{$email}'
			    ");
			    if (!$link->isQueryResultValid($result)){
					Log::warning("Не обнов. поле last_dt для пользователя \"{$email}\"");
			    }

   			}
   			catch (Exception $e){
   				// Требуется откат изменений
   				if ($e->getCode() == 1){
   					$result = $link->query("
				        DELETE FROM
				        	users
				        WHERE 
				        	user_id = {$userID}
				    ");
					if (!$link->isQueryResultValid($result)){
						Log::error("Ошибка удаления невалидного пользователя \"{$email}\"");
				    }
   				}

				Log::error($e->getMessage()." [{$email}]");
				$response["msg"] = $e->getMessage();
   			}

		    $link->disconnect();
		}
		else {
			Log::warning("Переданы неверные значения email&pass&nick");
			if (!$email){
				$response["msg"] = "Неверный формат email";
			}
		}
	}
	else {
		Log::debug("Попытка регистрации ранее аутентифиц. пользователя \"{$nick}\"");
	}


	header('Content-type: application/json');
	echo json_encode($response);
?>