<?php
    // DEBUG METHODS   

    function getAllFriends($userID){
        global $link;

        $result = $link->query("
            SELECT
                users.user_id, nick
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
            while ($data = $result->fetch_assoc()){
                $data["path"] = getUserAvatar($data["user_id"]);
                $output[] = $data;
            }
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
            while ($data = $result->fetch_assoc()){
                $data["friend"] = False;
                if (!is_null($data["friend_1_id"]) AND !is_null($data["friend_2_id"])){
                    $data["friend"] = True;
                }

                $data["avatar_path"] = getUserAvatar($data["user_id"]);
                $output[] = $data;
            }
        }
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