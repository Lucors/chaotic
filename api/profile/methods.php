<?php
    // PROFILE METHODS   

    function getProfileSettings(){
        global $link;

        $result = $link->query("
            SELECT
                allow_animation
            FROM
                users_settings
            WHERE
                user_id = {$_SESSION['user_id']}
        ");
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Ошибка получения настроек", 1);
        }
        return $result->fetch_assoc();
    }
    function setProfileSettings($settings){
        global $link;

        $query = "
            UPDATE
                users_settings
            SET
        ";
        foreach ($settings as $key => $value) {
            $query .= "{$key} = {$value},";
        }
        $query  = substr($query, 0, -1); // обрезаем запятую
        $query .= " WHERE user_id = {$_SESSION['user_id']}";

        $result = $link->query($query);
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка применения настроек");
        }
        return True;
    }
    function setProfileSettingsDefault(){
        global $link;

        $settings = getProfileSettings();
        foreach ($settings as $key => $value) {
            $settings[$key] = "DEFAULT({$key})";
        }
        return setProfileSettings($settings);
    }
?>