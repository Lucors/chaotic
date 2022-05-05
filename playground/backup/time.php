<?php
	// Сессия блокируется при SSE соединении
	// Все остальные входящие запросы, использующие сессию
	// остаются в состоянии pending до конца sse соединения
	if(!isset($_SESSION)){
		// Опция read_and_close позволит избежать этого
		// но теряет актуальность сессии
	    session_start(["read_and_close" => true]);
	}

    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");
    Log::msg("Запрошена sse служба времени [time.php]"); 
    $response = array();

	$link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
    $link->connect(); 

	header('Cache-Control: no-cache');
	header("Content-Type: text/event-stream\n\n");

	while (True){

		// $result = $link->query("
	 //        UPDATE
	 //            ssetest
	 //        SET
	 //        	dt = '".date("Y-m-d H:i:s")."'
	 //        WHERE
  //   			id = 1
	 //    ");
	 //    if ($link->isQueryResultValid($result)){
		// 	$response["result"] = True;
  //  		}
  //  		else {
		// 	$response["result"] = False;
		// 	$response["msg"] = "Ошибка записи времени";
  //  		}


	    $result = $link->query("
	        SELECT
	        	dt
	        FROM
	            ssetest
	        WHERE
    			id = 1
    		LIMIT 1
	    ");
   		if ($link->isMysqliResultValid($result)){
   			$data = $result->fetch_assoc();
			// $response["dt"] = $data["dt"];
			$response["dt"] = date("Y-m-d H:i:s");
			$response["result"] = True;
   		}
   		else {
			$response["result"] = False;
			$response["msg"] = "Ошибка получения времени";
   		}

		echo "data: ".json_encode($response)."\n\n";

		ob_end_flush();
		flush();
		sleep(1);
	}

	$link->disconnect();
?>