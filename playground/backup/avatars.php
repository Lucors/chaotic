<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("log.php");
    // require_once("general.php");
    // require_once("connect.php");
    Log::msg("Запрошен avatars.php"); 

	// Инициализируем переменную ответа. Предполагаем отрицательный ответ 
    $response = array(
    	"result" => False
    ); 

    function get(){
    	global $response;

		if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
			$response["path"] = General::getCorrectAvatarPath($_SESSION["user_id"]);
		}
		else {
			Log::warn("Недостаточно прав для запроса польз. аватара");
		}
		$response["result"] = True;
    }




	$op = filter_input(INPUT_POST, "op");
	if (!$op){
		$op = filter_input(INPUT_GET, "op");
	}

	if ($op){
		switch ($op) {
			case "get":
				get();
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>