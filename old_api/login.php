<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошен login.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False,
		"msg" => "Пользователь не найден"
    ); 

    function updateStageInMenu($link){
		if ($_SESSION["stage"] == USTAGE_INAUTH){
			$_SESSION["stage"] = USTAGE_INMENU;
		}
		
    	// Обновляем dt и stage
		$result = $link->query("
			UPDATE
				users
			SET
				last_dt = '".date('Y-m-d H:i:s')."',
				stage = {$_SESSION["stage"]}
			WHERE
    			email = '{$email}'
	    ");
	    if (!$link->isQueryResultValid($result)){
			Log::warning("Не обнов. поле last_dt для пользователя \"{$email}\"");
	    }
    }

	if (!isset($_SESSION["email"])){
		$email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
		$pass = filter_input(INPUT_POST, "pass");

		if ($email && $pass){
			session_unset();

			// Соединяемся с БД 
		    $link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		    $link->connect();

		    // Отправляем запрос на поиск пользователя с нужным логином
		    $result = $link->query("
        		SELECT
                	user_id, nick, passhash, role, stage
	            FROM
	                users
	            WHERE
	                email = '{$email}'
	            LIMIT 1
		    ");

		    // Если результат запроса валидный экземпляр mysqli_result (см. connect.php)
	   		if ($link->isMysqliResultValid($result)){
				$data = $result->fetch_assoc();
				// Проверяем совпадения пароля с хешом из БД
				if (password_verify($pass, $data["passhash"])){
					// Признак авторизации -- наличие аттрибута "email" в супер.глоб. массиве $_SESSION
					$_SESSION["email"] = $response["email"] = $email;
					$_SESSION["role"] = $response["role"] = $data["role"];
					$_SESSION["stage"] = $response["stage"] = $data["stage"];
					$_SESSION["nick"] = $response["nick"] = $data["nick"];
					$_SESSION["user_id"] = $response["user_id"] = $data["user_id"];
					// Вносим изменения в ответ
					$response["result"] = True;
					unset($response["msg"]);
					
					Log::msg("Аутентифицирован пользователь \"{$data["nick"]}\"");
					updateStageInMenu($link);
					
				}
				else {
					Log::error("Введен неверный пароль пользователя \"{$email}\"");
				}
	   		}
	   		else {
				Log::warning("Не найден пользователь \"{$email}\"");
	   		}
		    $link->disconnect();
		}
		else {
			Log::warning("Переданы неверные значения email&pass");
			if (!$email){
				$response["msg"] = "Неверный формат email";
			}
		}
	}
	else {
		$response["result"] = True;
		$response["role"] = $_SESSION["role"];
		$response["nick"] = $_SESSION["nick"];
		$response["user_id"] = $_SESSION["user_id"];
		$response["stage"] = $_SESSION["stage"];
		$response["msg"] = "Вы уже вошли в систему";
		// Log::debug("Попытка аутен. ранее аутентифиц. пользователя \"{$_SESSION['nick']}\"");
		// $response["msg"] = "Если вы видите это сообщение, произошла ошибка при смене этапа игры.
		// 	<input type='button' class='b1 grey' value='Выйти' style='display: inline-block; width: auto;' onclick='$(\".do-logout\").click()'>";
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>