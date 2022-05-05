<?php
	if(!isset($_SESSION)){
	    session_start();
	}
    require_once("general.php");
    require_once("log.php");
    require_once("connect.php");
    Log::msg("Запрошен rating.php"); 

    $response = array(
    	"result" => False,
    	"msg" => "Ошибка получения рейтинга"
    );

    function getAll(){
    	global $response;

		if (isset($_SESSION["email"])){
			$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
			$link->connect();

	    	$result = $link->query("
	    		SELECT
	    			users.user_id, users.nick, ratings.total, ratings.won
	    		FROM
	    			ratings
	    		INNER JOIN 
	    			users
	    		ON
	    			ratings.user_id = users.user_id
	    		ORDER BY
	    			ratings.won
	    		DESC 
	    		LIMIT 100
	    	");
	    	if($link->isMysqliResultValid($result)){
	    		$response["ratings"] = array();
	    		$position = 1;
	    		while($data = $result->fetch_assoc()){
	    			$field = array();
	    			$field[] = $position;
	    			$field[] = $data["nick"];
	    			$field[] = $data["won"];
	    			$field[] = $data["total"];
	    			$response["ratings"][] = $field;
	    			
	    			if ($data["user_id"] == $_SESSION["user_id"]){
	    				$response["user_rating"] = $field;
	    			}
	    			$position += 1;
	    		}
	    		$response["result"] = True;
	    		unset($response["msg"]);
	    	}

	    	$link->disconnect();
		}
		else {
			Log::warning("Недостаточно прав для запроса рейтинга");
			$response["msg"] = "Пользователь не авторизирован";
		}
    }
    

    $method = General::getInputMethod();
    $op = filter_input($method, "op");

	if ($op){
		switch ($op) {
			case "getall":
				getAll();
				break;
			default:
				break;
		}
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>
