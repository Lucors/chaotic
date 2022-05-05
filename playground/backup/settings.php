<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("general.php");
    require_once("log.php");
    require_once("connect.php");
    Log::msg("Запрошен settings.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False
    ); 

	// Клиент ожидает ответ по типу: 
	// TypeId => (InputType, расшифровка, значение, name), 
	// где InputType: (number - Числовой выбор), (checkbox - Чекбокс), (text - Иное, текст) 
	$settings = array(
		// Пример:
		// "allowAnim" => array(1, "Разрешить анимации", 1)
	);

    // Общая схема установки/получения настроек:
    // set => {id => value}, get => [key]
    $set = filter_input(INPUT_POST, "set");
    $get = filter_input(INPUT_POST, "get");

    if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
		$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
		$link->connect();

	    if ($set){
	    	$query = "
	    		UPDATE settings_values SET `value` = CASE
	    	";

			$set = json_decode($set, True);
			if (is_null($set)){
				Log::warning("Ошибка декодирования json строки [set]");
				$response["msg"] = "Неверный set запрос";
			}
			else {
				foreach ($set as $id => $value) {
					$query .= " WHEN setting_type_id = {$id} AND user_id = {$_SESSION["user_id"]} THEN '{$value}'"; 
				}
				$query .= " ELSE `value` END";

				$result = $link->query($query);
			    if (!$link->isQueryResultValid($result)){
					Log::warning("Ошибка обновления настроек");
					$response["msg"] = "Ошибка обновления настроек";
			    }
			    else {
			    	$response["result"] = True;
					unset($response["msg"]);
					$get = "all";
			    }
			}
	    }


	    if ($get){
	    	$query = "
	    		SELECT
	    			settings_values.setting_type_id,
	    			settings_types.name, settings_types.decryption,
	    			settings_types.input_type, settings_values.value
	    		FROM
	    			settings_values
	    		INNER JOIN
	    			settings_types
	    		ON settings_values.setting_type_id = settings_types.setting_type_id
	    		WHERE
	    			settings_values.user_id = {$_SESSION['user_id']}
	    	";

	    	if ($get != "all"){
				$get = json_decode($get, True);
				if (is_null($get)){
					Log::warning("Ошибка декодирования json строки [get]");
					$response["msg"] = "Неверный get запрос";
				}
				else {
					$query .= " AND (";
					foreach ($get as $value) {
	    				$query .= "name == '$value'  OR"; 
					}
					$query = substr($query, 0, -2).")";
				}
	    	}

	    	$result = $link->query($query);
		    if (!$link->isMysqliResultValid($result)){
				Log::warning("Ошибка получения настроек");
				$response["msg"] = "Ошибка получения настроек";
		    }
		    else {
				while($data = $result->fetch_assoc()){
					$key = $data["setting_type_id"];
					$settings[$key] = array();
					$settings[$key][] = $data["input_type"];
					$settings[$key][] = $data["decryption"];
					$settings[$key][] = $data["value"];
					$settings[$key][] = $data["name"];
				}

				$response["result"] = True;
				$response["settings"] = $settings;
				unset($response["msg"]);
		    }
	    }

		$link->disconnect();
    }
    else {
    	Log::warning("Неавторизированный польз. попытался получить настройки");
    	$response["msg"] = "Пользователь не авторизирован";
    }

    header('Content-type: application/json');
	echo json_encode($response);
?>