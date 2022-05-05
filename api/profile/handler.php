<?php
    // PROFILE REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    // inherits user's methods
    require_once("user/methods.php");

    switch ($request["op"]) {
        case "get_friends":
            $response["friends_list"] = getAllUserFriends($_SESSION["user_id"]);
            if (empty($response["friends_list"])){
                $response["msg"] = "Друзья не найдены";
            }
            break;
        case "get_role":
            $response["user_role"] = getUserRole($_SESSION["user_id"]);
            break;
        case "get_avatar":
            $response["avatar_path"] = getUserAvatar($_SESSION["user_id"]);
            break;
        case "get_settings":
            $response["settings_data"] = getProfileSettings();
            break;
        case "set_settings":
            if (!isset($request["settings_data"])){
                throw new Brake("Заполните поле данных настроек (settings_data)");
            }
            setProfileSettings($request["settings_data"]);
            break;
        case "setdefault_settings":
            setProfileSettingsDefault();
            break;
        case "set_stage":
            if (!isset($request["stage"])){
                throw new Brake("Не задан параметр USER_STAGE (stage)");
            }
            setUserStage($_SESSION["user_id"], $request["stage"]);
            break;
        default:
            $response["result"] = False;
            break;
    }
?>