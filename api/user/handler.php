<?php
    // USER REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    
    switch ($request["op"]) {
        // case "getself_friends":
        //     $response["friends_list"] = getAllUserFriends($_SESSION["user_id"]);
        //     if (empty($response["friends_list"])){
        //         $response["msg"] = "Друзья не найдены";
        //     }
        //     break;
        case "getby_query":
            if (!isset($request["query"])){
                throw new Brake("Пустой запрос", -1);
            }
            $response["users_list"] = getUserByQuery($request["query"]);
            if (empty($response["users_list"])){
                $response["msg"] = "Не найдено";
            }
            break;
        case "getby_room":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            $response["users_list"] = getRoomUsers($request["room_id"]);
            break;
        case "getsome":
            if (!isset($request["list"])){
                throw new Brake("Не задан параметр USERS_ID_LIST (list)");
            }
            $response["users_list"] = getSomeUsers($request["list"]);
            break;
        // case "set_stage":
        //     if (!isset($request["stage"])){
        //         throw new Brake("Не задан параметр USER_STAGE (stage)");
        //     }
        //     setUserStage($request["stage"]);
        //     break;
        // case "getself_role":
        //     $response["user_role"] = getUserRole($_SESSION["user_id"]);
        //     break;
        // case "getself_avatar":
        //     $response["avatar_path"] = getUserAvatar($_SESSION["user_id"]);
        //     break;
        default:
            $response["result"] = False;
            break;
    }
?>