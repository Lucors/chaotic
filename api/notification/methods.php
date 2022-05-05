<?php
    // NOTIF. METHODS   

    function getAllNotifications($userID){
        global $link;

        $result = $link->query("
            SELECT
                notification_id as notif_id, type,
                users.nick, users.user_id
            FROM
                notifications
            INNER JOIN
                users
            ON
                notifications.sender_id = users.user_id
            WHERE
                notifications.recipient_id = {$userID}
        ");

        $output = array();
        if ($link->isMysqliResultValid($result)){
            while ($data = $result->fetch_assoc()){
                $data["path"] = General::getCorrectAvatarPath($data["user_id"]);
                $output[] = $data;
            }
        }
        return $output;
    }

    function setNotification($sender, $target, $type, $content = null){
        global $link;

        // TODO: fix magic number 
        // Добавление в друзья 
        if ($type == 1){
            $result = $link->query("
                SELECT 
                    sender_id, recipient_id, type
                FROM
                    notifications
                WHERE 
                        ((sender_id = {$sender} AND recipient_id = {$target})
                    OR
                        (sender_id = {$target} AND recipient_id = {$sender}))
                    AND
                        type = 1
            ");
            if ($link->isQueryResultValid($result)){
                return True;
            }
        }

        if (!$content){
            $content = "null";
        }
        $result = $link->query("
            INSERT INTO
                notifications (sender_id, recipient_id, type, content) 
            VALUES
                ({$sender}, {$target}, {$type}, {$content})
        ");

        if ($link->isQueryResultValid($result)){
            return True;
        }
        return False;
    }


    function answerNotification($notifID, $answer){
        global $link;

        $result = $link->query("
            SELECT
                type, sender_id, recipient_id
            FROM
                notifications
            WHERE
                notifications.notification_id = {$notifID}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Уведомление не найдено (nid={$notifID})");
        }
        $data = $result->fetch_assoc();
        if ($data["recipient_id"] != $_SESSION['user_id']){
            throw new Brake("Уведомление устарело (nid={$notifID})");
        }

        $result = $link->query("
            DELETE FROM
                notifications
            WHERE
                notifications.notification_id = {$notifID}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка ответа на уведомление (nid={$notifID})");
        }

        if ($answer == 1){
            if ($data["type"] == 1){
                $result = $link->query("
                    INSERT INTO
                        friends
                    VALUES 
                        ({$data["recipient_id"]}, {$data['sender_id']})
                ");
                if (!$link->isQueryResultValid($result)){
                    throw new Brake("Ошибка добавления в друзья");
                }
            }
            else if ($data["type"] == 2) {
                // TODO: Тут переброс в комнату нужно сделать
            }
        }
        return True;
    }    
?>