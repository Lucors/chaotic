<?php
    // TOPIC REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");

    switch ($request["op"]) {
        case "getall":
            $response["topics_list"] = getAllTopics();
            break;
        case "getby_room":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            $response["topics_list"] = getRoomTopics($request["room_id"]);
            break;
        default:
            $response["result"] = False;
            break;
    }
?>