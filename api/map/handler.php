<?php
    // MAP REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");

    switch ($request["op"]) {
        case "get":
            if (!isset($request["map_id"])){
                throw new Brake("Не задан параметр MAP_ID (map_id)");
            }
            $response["map_data"]   = getMapData($request["map_id"]);
            break;
        case "getall":
            $response["maps_list"] = getAllMaps();
            break;
        case "get_scheme":
            if (!isset($request["map_id"])){
                throw new Brake("Не задан параметр MAP_ID (map_id)");
            }
            $response["scheme"]     = getMapScheme($request["map_id"], true);
            $response["types_list"] = getMapNodeTypes();
            break;
        case "get_node_types":
            $response["types_list"] = getMapNodeTypes();
            break;
        case "get_positions":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            $response["pos_list"] = getUsersPositions($request["room_id"]);
            break;
        case "move":
            if (!isset($request["map_id"])){
                throw new Brake("Не задан параметр MAP_ID (map_id)");
            }
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            $response["movement"] = moveUserAcrossMap($request["map_id"], 
                                                        $request["room_id"], $_SESSION["user_id"]);
            break;
        default:
            $response["result"] = False;
            break;
    }
?>