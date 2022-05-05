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

    if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
	    $get = filter_input(INPUT_POST, "get"); //$get = "all"

	    if ($get == "all"){
	    	$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
			$link->connect();

	    	$result = $link->query("
	    		SELECT
	    			user_id, nick, rating_total, rating_won
	    		FROM
	    			users
	    		ORDER BY
	    			rating_won
	    		DESC 
	    	");
	    	if($link->isMysqliResultValid($result)){
	    		$response["ratings"] = array();
	    		$position = 1;
	    		while($data = $result->fetch_assoc()){
	    			if ($position < 501){
		    			$field = array();
		    			$field[] = $position;
		    			$field[] = $data["nick"];
		    			$field[] = $data["rating_won"];
		    			$field[] = $data["rating_total"];
		    			$response["ratings"][] = $field;
	    			}
	    			
	    			if ($data["user_id"] == $_SESSION["user_id"]){
		    			$field = array();
		    			$field[] = $position;
		    			$field[] = $data["nick"];
		    			$field[] = $data["rating_won"];
		    			$field[] = $data["rating_total"];
	    				$response["user_rating"] = $field;
	    			}

	    			$position += 1;
	    		}
	    		$response["result"] = True;
	    		unset($response["msg"]);
	    	}

	    	$link->disconnect();
	    }
	}
	else {
    	Log::warning("Неавторизированный польз. попытался получить рейтинги");
		$response["msg"] = "Неавторизированный пользователь";
	}

    header('Content-type: application/json');
	echo json_encode($response);
?>