<?php
    // MAP METHODS

    function getAllMaps(){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT
                map_id, name
            FROM
                maps
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения списка карт");
        }
        while ($data = $result->fetch_assoc()){
            // Зачем?
            // $map = array();
            // $map[] = $data["map_id"];
            // $map[] = $data["name"];
            $output[] = $data;
        }
        return $output;
    }

    function getMapData($mapID){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT
                map_id, name, sprites_path
            FROM
                maps
            WHERE
                map_id = {$mapID}
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения данных комнаты");
        }
        $output = $result->fetch_assoc();
        $output["sprites_path"] = General::getSpritesPath($output["sprites_path"]);
        return $output;
    }

    function getMapScheme($mapID, $json=false){
        // $output         = array();
        $spritesPath    = getMapData($mapID)["sprites_path"];
        $schemeFilename = __DIR__."/../../".$spritesPath."map-scheme.php";
        if (!file_exists($schemeFilename)){
            throw new Brake($schemeFilename);
        }
        require_once($schemeFilename);
        if ($json){
            return json_encode($mapScheme);
        }
        return $mapScheme;
        // $output["types_list"] = getMapNodeTypes();
        // $output["scheme"]  = file_get_contents($schemeFilename);
        // unset($output["map_data"]);
        // return $output;
    }

    function getMapNodeTypes(){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT 
                *
            FROM
                node_types
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения типов узлов");
        }
        while ($data = $result->fetch_assoc()){
            $output[$data["node_types_id"]] = array($data["name"], $data["sprite"]);
        }
        return $output;
    }
    
    function getUsersPositions($roomID){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT 
                ingame_position.user_id as uid, position, ingame_steps_order.color
            FROM
                ingame_position
            INNER JOIN 
                ingame_steps_order
            ON
                ingame_position.user_id = ingame_steps_order.user_id
            WHERE
                ingame_position.room_id = {$roomID}
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения позиций игроков");
        }
        while ($data = $result->fetch_assoc()){
            $output[] = $data;
        }
        return $output;
    }

    function getUserPosition($userID){
        global $link;

        $result = $link->query("
            SELECT 
                room_id, position, dice
            FROM
                ingame_position
            WHERE
                ingame_position.user_id = {$userID}
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения позиции игрока");
        }
        return $result->fetch_assoc();
    }

    function moveUserAcrossMap($mapID, $roomID, $userID){
        global $link;

        $output = array(
            "dice"  => 0,
            "path"  => array()
        );
        $mapScheme      = getMapScheme($mapID);
        $position       = getUserPosition($userID)["position"];
        // $output["dice"] = rand(1, 4);
        $output["dice"] = rand(1, 2);
        $counter        = 0;
        $node           = $mapScheme["nodes"][$position];
        // $nextNode       = null;
        // $newStepStage   = SSTAGE_MOVE_END;
        while($counter < $output["dice"]){
            switch ($node["type"]) {
                case NODE_START:
                case NODE_STEP:
                case NODE_TP:
                    $position = $node["next"];
                    break;
                case NODE_PRISON:
                    break;
                case NODE_FORK:
                    // $forkSolution = rand(0, 1);
                    if (rand(1, 2) == 1){
                        $position = $node["next"];
                    }
                    else {
                        $position = $node["alt"];
                    }
                    break;
                case NODE_FINISH:
                    $position  = "finish";
                    $counter   = $output["dice"]+2;
                    finishGame($roomID, $userID);
                    break;
                default:
                    // throw new Brake("Неизвестный тип ячейки [{$node['type']}]");
                    break;
            }
            $node = $mapScheme["nodes"][$position];
            $output["path"][] = $position;
            switch ($node["type"]) {
                case NODE_PRISON:
                    if ($counter == $output["dice"]-1){
                        $output["path"][] = $node["next"];
                    }
                    break;
                case NODE_TP:
                    if ($counter == $output["dice"]-1){
                        $output["path"][] = $node["alt"];
                    }
                    break;
                case NODE_FINISH:
                    $position  = "finish";
                    $counter   = $output["dice"]+2;
                    finishGame($roomID, $userID);
                    break;
            }
            $counter++;
        }

        $position = end($output["path"]);
        $result = $link->query("
			UPDATE
                ingame_position
			SET
                dice = {$output['dice']},
                position = '{$position}'
            WHERE 
                ingame_position.user_id = {$userID}

		");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка обновления позиции игрока");
        }

        $result = $link->query("
            UPDATE
                ingame_steps
            SET
                stage = ".SSTAGE_MOVE_END."
            WHERE 
                ingame_steps.room_id = {$roomID}

        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка обновления игрового шага");
        }

        return $output;
    }

    function finishGame($roomID, $winnerID){
        global $link;
        // SET ROOM GAME END 
        $result = $link->query("
            UPDATE
                rooms
            SET
                stage = ".RSTAGE_GAME_END."
            WHERE 
                rooms.room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка конца игры #1");
        }

        $result = $link->query("
            SELECT 
                ratings.user_id, ratings.won, ratings.total
            FROM
                ratings
            INNER JOIN 
                ingame_position
            ON
                ingame_position.user_id = ratings.user_id
            WHERE
                ingame_position.room_id = {$roomID}
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка выставления рейтинга #1");
        }

        $totalUpdQuery = "
            UPDATE
                ratings
            SET
                total = CASE user_id
        ";
        $winnerScore = 0;
        while ($data = $result->fetch_assoc()){
            if ($data["user_id"] == $winnerID){
                $winnerScore = $data["won"] + 1;
            }
            $data['total']++;
            $totalUpdQuery .= "
                WHEN {$data['user_id']} THEN {$data['total']}
            ";
        }
        $totalUpdQuery .= " ELSE total END";
        // Log::warning($totalUpdQuery);
        $result = $link->query($totalUpdQuery);
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка выставления рейтинга #2");
        }

        if ($winnerScore > 0){
            $result = $link->query("
                UPDATE
                    ratings
                SET
                    won = {$winnerScore}
                WHERE 
                    ratings.user_id = {$winnerID}
            ");
            if (!$link->isQueryResultValid($result)){
                throw new Brake("Ошибка выставления рейтинга #3");
            }
        }
        
        Log::msg("Игра окончена: {room_id: {$roomID}, winner_id: {$winnerID}}");
    }
?>