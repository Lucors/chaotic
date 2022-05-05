<?php
    // MESSAGES REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    
    switch ($request["op"]) {
        case "getall":
            if (!isset($request["target"])){
                throw new Brake("Неверный id пользователя");
            }
            $response["msg_list"] = getAllMessages(
                    $_SESSION["user_id"], $request["target"]
                );
            break;

        case "set":
            if (!isset($request["target"]) || !isset($request["content"])){
                throw new Brake("Заполните поля target AND content");
            }
            $response["message_id"] = setMessage(
                    $_SESSION["user_id"], 
                    $request["target"], 
                    $request["content"]
                );
            break;
        default:
            $response["result"] = False;
            break;
    }
?>