<?php
    // ROOM REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    
    switch ($request["op"]) {
        case "getall":
            $response["rooms_list"] = getAllRooms();
            break;
        case "get":
            // inherits topic's methods
            require_once("topic/methods.php");
            require_once("user/methods.php");

            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            $response["room_id"]     = $request["room_id"];
            $response["room_data"]   = getRoomByID($request["room_id"]);
            $response["topics_list"] = getRoomTopics($request["room_id"]);
            $response["users_list"]  = getRoomUsers($request["room_id"]);
            break;
        case "create":
            // inherits topic's methods
            require_once("topic/methods.php");
            require_once("user/methods.php");

            if (!isset($request["room_data"])){
                throw new Brake("Не задан параметр ROOM_DATA (room_data)");
            }
            $response["room_id"]     = createRoom($request["room_data"]);
            $response["room_data"]   = getRoomByID($response["room_id"]);
            $response["topics_list"] = getRoomTopics($response["room_id"]);
            $response["users_list"]  = getRoomUsers($response["room_id"]);
            break;
        case "exit":
            exitCurrentRoom();
            break;
        case "enter":
            // inherits user's methods
            require_once("user/methods.php");
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            if (!isset($request["password"])){
                throw new Brake("Не задан параметр PASSWORD (password)");
            }
            enterRoom($request["room_id"], $request["password"]);
            $response["users_list"] = getRoomUsers($request["room_id"]);
            break;
        case "set_room_ready":
            // inherits user's methods
            require_once("user/methods.php");

            if (!isset($request["ready"])){
                throw new Brake("Не задан параметр READY (ready)");
            }

            if ($request["ready"] == "true"){
                $request["ready"] = USTAGE_INROOM_READY;
            }
            else {
                $request["ready"] = USTAGE_INROOM_NOTREADY;
            }

            setUserStage($_SESSION["user_id"], $request["ready"]);
            break;
        case "set_game_ready":
            // inherits user's methods
            require_once("user/methods.php");

            setUserStage($_SESSION["user_id"], USTAGE_INGAME_READY);
            break;
        case "start_room":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            startRoom($request["room_id"]);
            break;
        case "get_order":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            $response["order_list"] = getUsersOrder($request["room_id"]);
            break;
        case "start_game":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            startGame($request["room_id"]);
            break;
        case "push_order":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            pushStepOrder($request["room_id"]);
            break;
        case "join":
            // inherits user's methods
            require_once("user/methods.php");
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            if (!isset($request["password"])){
                $request["password"] = null;
                // throw new Brake("Не задан параметр PASSWORD (password)");
            }
            enterRoom($request["room_id"], $request["password"]);
            header("Location: https://chaoticgame.ru/");
            exit;
            // break;
        default:
            $response["result"] = False;
            break;
    }
?>