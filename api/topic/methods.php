<?php
    // TOPIC METHODS

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

    function getAllTopics(){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT
                topic_id, name, icon_path
            FROM
                topics
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Ошибка получения тем вопросов");
        }
        // output = [[topic_id, name, icon_path], [...]]
        while ($data = $result->fetch_assoc()){
            // $topic = array();
            // $topic["topic_id"] = $data["topic_id"];
            // $topic["name"] = $data["name"];
            $data["icon_path"] = General::getCorrectTopicIcoPath($data["icon_path"]);
            $output[] = $data;
        }
        return $output;
    }

    function getRoomTopics($roomID){
        global $link;

        $output = array();
        $result = $link->query("
            SELECT
                topics.topic_id, topics.name, topics.icon_path
            FROM
                rooms_topics
            INNER JOIN
                topics
            ON
                topics.topic_id = rooms_topics.topic_id
            WHERE 
                rooms_topics.room_id = {$roomID}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Ошибка получения тем комнаты");
        }
        // output = [[topic_id, name, icon_path], [...]]
        while ($data = $result->fetch_assoc()){
            // $topic = array();
            // $topic[] = $data["topic_id"];
            // $topic[] = $data["name"];
            $data["icon_path"] = General::getCorrectTopicIcoPath($data["icon_path"]);
            $output[] = $data;
        }
        return $output;
    }
?>