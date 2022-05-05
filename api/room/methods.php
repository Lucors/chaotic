<?php
    // ROOM METHODS   

    function getAllRooms(){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT
                rooms.room_id, rooms.name, privacy, maps.name as map_name, COUNT(*) as playes_num
            FROM
                rooms
            INNER JOIN
                users
            ON
                users.room_id = rooms.room_id
            INNER JOIN
                maps
            ON
                maps.map_id = rooms.map_id
            WHERE
                rooms.stage = 0
            GROUP BY
                room_id, rooms.name, privacy
            LIMIT 40
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения списка комнат");
        }
        while ($data = $result->fetch_assoc()){
            if ($data["playes_num"] < 4){
                $output[] = $data;
            }
        }
        return $output;
    }

    function getRoomByID($id){
        global $link;

        $result = $link->query("
            SELECT
                rooms.room_id, rooms.name, privacy, rooms.step_time, 
                rooms.step_max, maps.name as map_name, maps.sprites_path, 
                maps.map_id, COUNT(*) as playes_num
            FROM
                rooms
            INNER JOIN
                users
            ON
                users.room_id = rooms.room_id
            INNER JOIN
                maps
            ON
                maps.map_id = rooms.map_id
            WHERE 
                rooms.room_id = {$id}
            GROUP BY
                room_id, rooms.name, privacy
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Ошибка получения данных комнаты #1");
        }
        $output = $result->fetch_assoc();

        $result = $link->query("
            SELECT
                users.nick as creator_name, users.user_id as creator_id
            FROM
                users
            INNER JOIN
                rooms
            ON
                users.user_id = rooms.creator_id
            WHERE 
                rooms.room_id = {$output['room_id']}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Ошибка получения данных комнаты #2");
        }
        $output = array_merge($output, $result->fetch_assoc());
        
        $output["sprites_path"] = General::getSpritesPath($output["sprites_path"]);
        $output["map_preview"] = "map-preview.png";
        return $output;
    }


    function createRoom($roomData){
        global $link;

        try {
            $result = $link->query("
                SELECT
                    room_id
                FROM 
                    users
                WHERE
                    user_id = {$_SESSION['user_id']}
            ");
            if ($link->isMysqliResultValid($result)){
                // TODO: Разберитесь с isMysqliResultValid
                $data = $result->fetch_assoc();
                if (!is_null($data["room_id"])){
                    throw new Brake("Вы уже в комнате. Обновите страницу");
                }
            }

            $result = $link->query("
                SELECT
                    room_id, creator_id
                FROM 
                    rooms
                WHERE
                    creator_id = {$_SESSION['user_id']}
            ");
            if ($link->isMysqliResultValid($result)){
                $data = $result->fetch_assoc();
                if (!is_null($data["creator_id"])){
                    throw new Brake("У вас уже есть комната. Обновите страницу");
                    // TODO: Исправить поведение выхода из аккаунта
                    forceExitRoom($data["room_id"], $link);
                }
            }


            // roomData{name, step_time, privacy, password, topics[]}
            if($roomData["privacy"] == 1 && empty($roomData["password"])){
                throw new Brake("Не передан пароль для закрытой комнаты");
            }
            if ($roomData["step_time"] > 40 || $roomData["step_time"] < 10){
                throw new Brake("Неверное значение \"Время ответа\"");
            }

            // Создаем запись в таблице комнат
            $result = $link->query("
                INSERT INTO
                    rooms (name, privacy, password, step_time, stage, creator_id, map_id)
                VALUES 
                    ('{$roomData['name']}', {$roomData['privacy']}, '{$roomData['password']}',
                        {$roomData['step_time']}, ".RSTAGE_LOBBY_WAIT.", {$_SESSION['user_id']}, {$roomData['map_id']})
            ");
            if (is_null($result)){
                if ($link->mysqli->errno == 1062){
                    throw new Brake("Название комнаты уже занято");
                }
                throw new Brake("Ошибка создания комнаты");
            }
            $roomID = $link->mysqli->insert_id;


            // Создаем записи в таблице Тем Комнат
            $query = "
                INSERT INTO
                    rooms_topics (room_id, topic_id)
                VALUES 
            ";
            $i = 0;
            foreach ($roomData["topics"] as $value) {
                if ($i == 4){
                    break;
                }

                $query .= "({$roomID}, {$value}),";
                $i += 1;
            }
            $query = substr($query, 0, -1); // Обрезаем запятую
            $result = $link->query($query);
            if (!$link->isQueryResultValid($result)){
                throw new Brake("Ошибка записи тем комнаты", 1);
            }


            // Создаем запись в таблице пользователей
            // Выставляем USTAGE_INROOM_READY т.к. создатель
            $result = $link->query("
                UPDATE
                    users
                SET 
                    room_id = {$roomID},
                    stage = ".USTAGE_INROOM_READY."
                WHERE
                    user_id = {$_SESSION['user_id']}
            ");
            if (!$link->isQueryResultValid($result)){
                throw new Brake("Ошибка добавления игрока в комнату", 1);
            }
            
            Log::msg("Создана комната: {
                room_id:    {$roomID}, 
                creator_id: {$_SESSION['user_id']}, 
                privace:    {$roomData['privacy']},
                name:       \"{$roomData['name']}\", 
                map_id:     {$roomData['map_id']}
            }");
            return $roomID;
        }
        catch (Brake $e) {
            if ($e->getCode() == 1){
                $link->query("
                    DELETE FROM
                        rooms
                    WHERE
                        room_id = {$roomID}
                ");
                $link->query("
                    DELETE FROM
                        rooms_topics
                    WHERE
                        room_id = {$roomID}
                ");
            }
            throw $e;
            // throw new Brake($e->getMessage(), LOG_LVL_ERR);
        }
    }

    function exitOwnRoom($roomID, $creatorID){
        global $link;

        $result = $link->query("
            DELETE FROM 
                rooms_topics
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не удалось удалить темы комнаты [rooms_topics]");
            $response["msg"] = "Не удалось удалить темы комнаты";
        }

        $result = $link->query("
            DELETE FROM 
                ingame_position
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не удалось очистить метки позиций [ingame_position]");
            $response["msg"] = "Не удалось очистить метки позиций";
        }
        $result = $link->query("
            DELETE FROM 
                ingame_steps
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не удалось очистить буфер шагов [ingame_steps]");
            $response["msg"] = "Не удалось очистить буфер шагов";
        }
        $result = $link->query("
            DELETE FROM 
                ingame_steps_order
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не удалось очистить очередь ходов [ingame_steps_order]");
            $response["msg"] = "Не удалось очистить очередь ходов";
        }

        $result = $link->query("
            UPDATE
                users
            SET 
                room_id = null,
                stage = ".USTAGE_INMENU."
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не удалось set room_id = null for users in room [exitRoom]");
            $response["msg"] = "Не удалось выбросить игроков из комнаты";
        }

        $result = $link->query("
            DELETE FROM 
                rooms
            WHERE
                room_id = {$roomID}
                AND 
                creator_id = {$creatorID} 
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не удалось удалить вашу комнату [exitRoom] {room_id: {$roomID}}");
            $response["msg"] = "Не удалось удалить вашу комнату";
        }
        Log::msg("Удалена комната: {room_id: {$roomID}}");
    }
    function exitCurrentRoom(){
        global $link;

        $result = $link->query("
            SELECT
                room_id
            FROM 
                users
            WHERE
                user_id = {$_SESSION['user_id']}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Пользователь не в комнате");
        }
        $roomID = $result->fetch_assoc()["room_id"];

        $roomData = null;
        $result = $link->query("
            SELECT
                rooms.room_id, creator_id,
                COUNT(*) as playes_num, rooms.stage
            FROM 
                rooms
            INNER JOIN
                users
            ON
                users.room_id = rooms.room_id
            WHERE
                rooms.room_id = {$roomID}
        ");
        if ($link->isMysqliResultValid($result)){
            $roomData = $result->fetch_assoc();
        }

        $result = $link->query("
            UPDATE
                users
            SET 
                room_id = null,
                stage = ".USTAGE_INMENU."
            WHERE
                user_id = {$_SESSION['user_id']}
        ");
        if (!$link->isQueryResultValid($result)){
            // throw new Brake("Не удалось установить room_id в null", 1);
        }
        $roomData["playes_num"] -= 1;

        // УДАЛЯЕМ ИГРОКА ИЗ ОЧЕРЕДИ 
        $result = $link->query("
            DELETE FROM 
                ingame_steps_order
            WHERE
                user_id = {$_SESSION['user_id']}
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Игрок не исключен из очереди ходов [ingame_steps_order]");
            $response["msg"] = "Игрок не исключен из очереди ходов";
        }
        Log::msg("Игрок покинул комнату: {room_id: {$roomID}, user_id: {$_SESSION['user_id']}}");

        if (!is_null($roomData)){
            $creatorID = $roomData["creator_id"];

            if (($roomData["stage"] > RSTAGE_LOBBY_WAIT && $roomData["playes_num"] <= 1) 
                || ($creatorID == $_SESSION['user_id'])){
                exitOwnRoom($roomID, $creatorID);
            }
        }
    }


    function enterRoom($roomID, $password){
        global $link;

        $result = $link->query("
            SELECT
                rooms.creator_id, rooms.privacy,
                rooms.password, rooms.stage,
                COUNT(*) as playes_num
            FROM
                rooms
            INNER JOIN
                users
            ON
                users.room_id = rooms.room_id
            WHERE 
                rooms.room_id = {$roomID}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Не удалось проверить доступность комнаты");
        }

        $roomData = $result->fetch_assoc();
        if (is_null($roomData["creator_id"])){
            throw new Brake("Комната не найдена");
        }
        if ($roomData["stage"] != RSTAGE_LOBBY_WAIT){
            throw new Brake("Игра уже началась");
        }
        if ($roomData["playes_num"] >= 4){
            throw new Brake("Недостаточно мест");
        }

        if ($roomData["privacy"] == 2){
            // inherits user's methods
            require_once("user/methods.php");
            if (!checkUserFriend($_SESSION['user_id'], $roomData["creator_id"])){
                throw new Brake("Доступ запрещен");
            }
        }
        else if ($roomData["privacy"] == 1){
            if ($password != $roomData["password"]){
                throw new Brake("Неверный пароль");
            }
        }

        $result = $link->query("
            SELECT
                room_id
            FROM 
                users
            WHERE
                user_id = {$_SESSION['user_id']}
        ");
        if ($link->isMysqliResultValid($result)){
            // TODO: Разберитесь с isMysqliResultValid
            $data = $result->fetch_assoc();
            if (!is_null($data["room_id"])){
                throw new Brake("Вы уже в комнате. Обновите страницу");
            }
        }

        $result = $link->query("
            UPDATE
                users
            SET 
                room_id = {$roomID},
                stage = ".USTAGE_INROOM_NOTREADY."
            WHERE
                user_id = {$_SESSION['user_id']}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка добавления игрока в комнату");
        }
        Log::msg("Игрок вошел в комнату: {room_id: {$roomID}, user_id: {$_SESSION['user_id']}}");
    }

    function startRoom($roomID){
        global $link;

        // $output = array();
        $result = $link->query("
            SELECT
                rooms.stage, COUNT(*) as playes_num
            FROM
                rooms
            INNER JOIN
                users
            ON
                users.room_id = rooms.room_id
            WHERE 
                rooms.room_id = {$roomID}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Не удалось проверить условий запуска #1");
        }
        $playesNum = $result->fetch_assoc()["playes_num"];
        if ($playesNum < 2){
            throw new Brake("Недостаточно игроков", 1);
        };

        $result = $link->query("
            SELECT
                users.user_id, users.stage
            FROM
                users
            WHERE 
                users.room_id = {$roomID}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Не удалось проверить условие запуска #2");
        }
        $userList = array();
        while ($data = $result->fetch_assoc()){
            $userList[] = $data;
            if ($data["stage"] == 3){
                throw new Brake("Дождитесь готовности игроков!", 1);
            }
        }

    // POSITIONS
        $query = "
            INSERT INTO
                ingame_position (room_id, user_id, position)
            VALUES 
        ";
        foreach ($userList as $value) {
            $query .= "({$roomID}, {$value['user_id']}, 'start'),";
        }
        $query = substr($query, 0, -1); // Обрезаем запятую
        $result = $link->query($query);
        if (is_null($result)){
            throw new Brake("Ошибка создания метки позиции игрока");
        }

    // ORDER
        $order = array();
        $colors = array();
        for ($i = 1; $i <= 4; $i++) {
            if ($i <= $playesNum){
                $order[] = $i;
            }
            $colors[] = $i;
        }
        shuffle($colors);
        shuffle($order);
        $query = "
            INSERT INTO
                ingame_steps_order (room_id, user_id, color, value)
            VALUES
        ";
        $i = 0;
        foreach ($userList as $value) {
            $query .= "({$roomID}, {$value['user_id']}, {$colors[$i]}, {$order[$i]}),";
            $i += 1;
        }
        $query = substr($query, 0, -1); // Обрезаем запятую
        $result = $link->query($query);
        if (is_null($result)){
            // if ($link->mysqli->errno == 1062){
            //     throw new Brake("Название комнаты уже занято");
            // }
            throw new Brake("Ошибка создания очереди шагов");
        }

    // STEP BUFFER
        $dt = date("Y-m-d H:i:s", strtotime("now")-50);
        $result = $link->query("
            INSERT INTO
                ingame_steps (room_id, order_value, start_dt)
            VALUES 
                ({$roomID}, 5, '{$dt}')
        ");
        if (is_null($result)){
            throw new Brake("Ошибка создания буфера шага");
        }


        $result = $link->query("
            UPDATE
                rooms
            SET 
                stage = ".RSTAGE_GAME_WAIT."
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Не удалось начать игру #1");
        }

        $result = $link->query("
            UPDATE
                users
            SET 
                stage = ".USTAGE_INGAME_LOADING."
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Не удалось начать игру #2");
        }
        Log::msg("Началась игра: {room_id: {$roomID}}");
    }


    function getUsersOrder($roomID){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT
                user_id as uid, color, value as order_value
            FROM
                ingame_steps_order
            WHERE
                room_id = {$roomID}
            ORDER BY
                value
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения очереди ходов");
        }
        while ($data = $result->fetch_assoc()){
            $output[] = $data;
        }
        return $output;
    }

    
    function startGame($roomID){
        global $link;
        
        $result = $link->query("
            UPDATE
                rooms
            SET 
                stage = ".RSTAGE_GAME_START."
            WHERE
                room_id = {$roomID}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Не удалось начать игру");
        }
    }


    function pushStepOrder($roomID){
        global $link;

		$result = $link->query("
			SELECT
				user_id as uid, ingame_steps_order.value as order_value
			FROM
				ingame_steps_order
			WHERE
				ingame_steps_order.room_id = {$roomID}
			ORDER BY
				ingame_steps_order.value
		");
		$orders = array();
		while($data=$result->fetch_assoc()){
			$orders[$data["order_value"]] = $data["uid"];
		}

		$result = $link->query("
			SELECT
				order_value
			FROM
				ingame_steps
			WHERE
				ingame_steps.room_id = {$roomID}
		");
		$lastOrderValue = $result->fetch_assoc()["order_value"];
		if (is_null($lastOrderValue)){
			$lastOrderValue = 5;
		}

		$query = "
			UPDATE
				ingame_steps
			SET
                stage = ".SSTAGE_ORDER.",
		";

		$orderKeys = array_keys($orders);

		$output["uid"] = $orders[$orderKeys[0]];
		$tmpQuery = "user_id = {$orders[$orderKeys[0]]}, order_value = {$orderKeys[0]}";
		foreach ($orders as $i => $value) {
			if ($i > $lastOrderValue){
				$output["uid"] = $value;
				$tmpQuery = "user_id = {$value}, order_value = {$i}";
				break;
			}
		}
		$query .= $tmpQuery;

		// if (end($orders)["order_value"] <= $lastOrderValue){
		// 	$output["uid"] = $orders[0]['uid'];
		// 	$query .= "user_id = {$orders[0]['uid']}, order_value = {$orders[0]['order_value']}";
		// }
		// else {
		// 	$output["uid"] = $orders[$lastOrderValue]['uid'];
		// 	$query .= "user_id = {$orders[$lastOrderValue]['uid']}, order_value = {$orders[$lastOrderValue]['order_value']}";
		// }
        
        //Пусть getUniqueQuestion решает значение start_dt
        // $dt = date("Y-m-d H:i:s", strtotime("now"));
		// $query .= " , start_dt = '{$dt}' WHERE ingame_steps.room_id = {$roomID}";

		$query .= " WHERE ingame_steps.room_id = {$roomID}";
		$result = $link->query($query);
		// return $output;
    }
    
?>