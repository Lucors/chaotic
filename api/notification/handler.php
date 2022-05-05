<?php
    // NOTIF. REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    
    switch ($request["op"]) {
        case "getall":
            $response["notif_list"] = getAllNotifications($_SESSION["user_id"]);
            if (empty($response["notif_list"])){
                $response["msg"] = "Нет новых уведомлений";
            }
            break;

        case "set":
            if (!isset($request["target"]) || !isset($request["type"])){
                throw new Brake("Заполните поля target AND type");
            }
            $content = null;
            if (isset($request["content"])){
                $content =& $request["content"];
            }

            $response["result"] = setNotification(
                    $_SESSION["user_id"], $request["target"],
                    $request["type"], $content
                );
            if (!$response["result"]){
                $response["msg"] = "Ошибка отправки приглашения";
            }
            break;

        case "answer":
            if (!isset($request["notif_id"]) || !isset($request["answer"])){
                throw new Brake("Заполните поля notif_id AND answer");
            }
            $response["result"] = answerNotification(
                    $request["notif_id"], $request["answer"]
                );
            break;
            
        default:
            $response["result"] = False;
            break;
    }
?>