<?php
	if(!isset($_SESSION)){
	    session_start(["read_and_close" => true]);
	}
    header("Cache-Control: no-cache");
    header("Content-Type: text/event-stream\n\n");
    require_once("../general.php");
    require_once("../log.php");
    require_once("../connect.php");
    Log::debug("Запущена sse слушатель уведомлений [deamon-notif-listen.php]"); 
    $response = array();
    $lastData = array();

    if (isset($_SESSION["email"]) && isset($_SESSION["user_id"])){
        $link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
        $link->connect(); 

        set_time_limit(120);
        echo "retry: 16\n\n";
        ob_flush();
        flush();

        while (true) {
            if (connection_aborted()){
                break;
            }
            $response["result"] = False;

            // $result = $link->query("
            //     SELECT
            //         last_dt
            //     FROM
            //         users
            //     WHERE
            //         user_id = {$_SESSION["user_id"]}
            //     LIMIT 1
            // ");
            // if ($link->isMysqliResultValid($result)){
            //     $now = time();
            //     $heartBeat = strtotime($result->fetch_assoc()["last_dt"]);
            //     if (($now - $heartBeat) < 50){
            //         set_time_limit(60);
            //     }
            // }

            // Проверка наличия непрочитанных сообщений
            $result = $link->query("
                SELECT
                    sender_id
                FROM
                    private_messages
                WHERE
                    recipient_id = {$_SESSION["user_id"]}
                    AND 
                    checked = 0
                GROUP BY
                    sender_id
            ");
            if ($link->isMysqliResultValid($result)){
                // Возвращать будем список ID отправителей
                $response["msgs"] = array();
                while ($data = $result->fetch_assoc()) {
                    $response["msgs"][] = $data["sender_id"];
                }

                $response["result"] = True;
            }

            // Проверка наличия необработанных уведомлений
            $result = $link->query("
                SELECT
                    notification_id
                FROM
                    notifications
                WHERE
                    recipient_id = {$_SESSION["user_id"]}
                LIMIT 1
            ");
            if ($link->isMysqliResultValid($result)){
                // Достаточно просто Да/Нет, когда речь идет о
                // уведомлениях, т.к. обработанные -- удаляются
                $response["notifs"] = True;
                $response["result"] = True;
            }

            // Отправляем данные, если есть изменения
            // if ($lastData != $response){
                // $lastData = $response;
                echo "data: ".json_encode($response)."\n\n";
            // }

            // echo "data: ".json_encode($response)."\n\n";
            ob_end_flush();
            flush();
            sleep(3);
            unset($response["msgs"]);
            unset($response["notifs"]);
        }

        $link->disconnect(); 
    }


?>