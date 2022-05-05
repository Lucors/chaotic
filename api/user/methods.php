<?php
    // USER METHODS   


    function handleUsersListResult($result, $forceFriends = False){
        global $link;

        $output = array();
        while ($data = $result->fetch_assoc()){
            if (($data["user_id"] != $_SESSION["user_id"])){
                $data["friend"] = True;
                if (!$forceFriends){
                    $data["friend"] = False;
                    if (!is_null($data["friend_1_id"]) AND !is_null($data["friend_2_id"])){
                        $data["friend"] = True;
                    }
                } 
            }
            unset($data["friend_1_id"]);
            unset($data["friend_2_id"]);

            $data["avatar_path"] = getUserAvatar($data["user_id"]);
            $output[] = $data;
        }
        return $output;
    }

    function checkUserFriend($userID, $target){
        global $link;

        $result = $link->query("
            SELECT
                users.user_id
            FROM
                friends
            INNER JOIN
                users
            ON
                users.user_id = friends.friend_1_id OR users.user_id = friends.friend_2_id
            WHERE
                    (friends.friend_1_id = {$userID} AND friends.friend_2_id = {$target})
                OR 
                    (friends.friend_1_id = {$target} AND friends.friend_2_id = {$userID})
        ");
        if (!$link->isMysqliResultValid($result)){
            return False;
        }
        return True;
    }

    function getAllUserFriends($userID){
        global $link;

        $result = $link->query("
            SELECT
                users.user_id, nick, friend_1_id, friend_2_id
            FROM
                friends
            INNER JOIN
                users
            ON
                users.user_id = friends.friend_1_id OR users.user_id = friends.friend_2_id
            WHERE
                    (friends.friend_1_id =  {$userID} 
                OR 
                    friends.friend_2_id = {$userID})
                AND 
                    users.user_id != {$userID} 
        ");
        $output = array();
        if ($link->isMysqliResultValid($result)){
            $output = handleUsersListResult($result);
        }
        return $output;
    }

    function getUserByQuery($query){
        global $link;

        if ($query[0] == "#"){
            $query = "users.user_id = ".substr($query, 1);
        }
        else {
            $query = "users.nick LIKE '%{$query}%'";
        }
        // Log::debug($query);

        $result = $link->query("
            SELECT
                user_id, nick, friend_1_id, friend_2_id
            FROM
                users
            LEFT JOIN friends ON
                (friend_1_id = {$_SESSION["user_id"]} AND friend_2_id = user_id)
                OR
                (friend_1_id = user_id AND friend_2_id = {$_SESSION["user_id"]})
            WHERE
                {$query}
                AND
                user_id != {$_SESSION["user_id"]}
            LIMIT 40
        ");

        $output = array();
        if ($link->isMysqliResultValid($result)){
            $output = handleUsersListResult($result);
        }
        return $output;
    }

    function setUserStage($userID, $stage){
        global $link;

        $result = $link->query("
            UPDATE
                users
            SET 
                stage = {$stage}
            WHERE
                user_id = {$userID}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка установки игроку stage");
        }
        if ($_SESSION["user_id"] == $userID){
            $_SESSION["stage"] = $stage;
        }
    }

    function getRoomUsers($roomID){
        global $link;

        $result = $link->query("
            SELECT
                user_id, nick, friend_1_id, friend_2_id
            FROM
                users
            LEFT JOIN friends ON
                (friend_1_id = {$_SESSION["user_id"]} AND friend_2_id = user_id)
                OR
                (friend_1_id = user_id AND friend_2_id = {$_SESSION["user_id"]})
            WHERE
                users.room_id = {$roomID}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Ошибка получения игроков комнаты");
        }
        $output = handleUsersListResult($result);
        return $output;
    }

    function getSomeUsers($userListID){
        global $link;

        $sqlInList = "";
        foreach ($userListID as $value) {
            $sqlInList .= "{$value},";
        }
        $sqlInList = substr($sqlInList, 0, -1); // Обрезаем запятую
        $result = $link->query("
            SELECT
                user_id, nick, friend_1_id, friend_2_id
            FROM
                users
            LEFT JOIN friends ON
                (friend_1_id = {$_SESSION["user_id"]} AND friend_2_id = user_id)
                OR
                (friend_1_id = user_id AND friend_2_id = {$_SESSION["user_id"]})
            WHERE
                users.user_id in ({$sqlInList})
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Ошибка получения списка игроков");
        }
        $output = handleUsersListResult($result);
        return $output;
    }

    function getUserRole($userID){
        global $link;

        $result = $link->query("
            SELECT
                role
            FROM
                users
            WHERE
                user_id = {$userID}
            LIMIT 1
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения user_role");
        }
        return $result->fetch_assoc()["role"];
    }

    function getUserAvatar($userID){
        return General::getCorrectAvatarPath($userID);
    }
?>