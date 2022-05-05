<?php
    // MESSAGES METHODS   

    function getAllMessages($userID, $targetID){
        global $link;

        $result = $link->query("
            SELECT
                *
            FROM
                private_messages
            WHERE
                (
                    (sender_id = {$targetID} AND recipient_id = {$userID})
                    OR
                    (sender_id = {$userID} AND recipient_id = {$targetID})
                )
            ORDER BY
                private_message_id
        ");

        $output = array();
        if ($link->isMysqliResultValid($result)){
            // TODO: Упростить этот бред
            // Определяем, есть ли новые сообщения для userID
            $haveUnchecked = False;
            // Собираем запрос обновления поля CHECKED у всех сообщений recipient_id == userID   
            $query = "
                UPDATE
                    private_messages
                SET checked = 1
                WHERE
                    recipient_id = {$userID} 
                    AND 
                    sender_id = {$targetID}
            ";
            while ($data = $result->fetch_assoc()) {
                $output[] = $data;
                if ($data["checked"] == 0 && $data['recipient_id'] == $userID){
                    // $query .= "
                    //     WHEN 
                    //         private_message_id = {$data['private_message_id']}
                    //     THEN 1
                    // ";
                    $haveUnchecked = True;
                }
            }
            // $query .= "ELSE `checked` END";

            // Обновляем состояния CHECKED
            if ($haveUnchecked){
                $result = $link->query($query);
                if (!$link->isQueryResultValid($result)){
                    throw new Brake("Ошибка обновления параметров сообщений");
                }
            }
        }
        return $output;
    }


    function setMessage($userID, $target, $content){
        global $link;

        $result = $link->query("
            INSERT INTO
                private_messages (sender_id, recipient_id, content, checked)
            VALUES 
                ({$userID}, {$target}, '{$content}', 0)
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка при добавлении сообщения в БД");
        }
        return $link->mysqli->insert_id;
    }

?>