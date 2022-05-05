<?php
    // RATING METHODS   

    function getAllRating($userID = null){
        global $link;

        $result = $link->query("
            SELECT
                users.user_id, users.nick, ratings.total, ratings.won
            FROM
                ratings
            INNER JOIN 
                users
            ON
                ratings.user_id = users.user_id
            ORDER BY
                ratings.won
            DESC 
            LIMIT 100
        ");

        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения таблицы рейтинга");
        }
        $output = array("list" => array());
        $position = 1;
        while($data = $result->fetch_assoc()){
            // $field = array();
            // $data["pos"] = $position;
            // $field["nick"] = $data["nick"];
            // $field["won"] = $data["won"];
            // $field["total"] = $data["total"];         
            $data["pos"] = $position;
            $output["list"][] = $data;
            
            if ($data["user_id"] == $userID){
                $output["user_rating"] = $data;
            }
            $position += 1;
        }

        if (!is_null($userID)){
            return $output;
        }
        return $output["list"];
    }

?>