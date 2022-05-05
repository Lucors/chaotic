<?php
    // AUTH METHODS   

    $psList = shell_exec("ps -A");
    $psCount = count(preg_split("/\n/", $psList)) -2;

    function updateStageInMenu($email){
        global $link;

        // если ранее чел был не авторизирован, то USTAGE_INMENU
        if ($_SESSION["stage"] == USTAGE_INAUTH){
            $_SESSION["stage"] = USTAGE_INMENU;
        }
        
        // Обновляем dt и stage
        $result = $link->query("
            UPDATE
                users
            SET
                last_dt = '".date('Y-m-d H:i:s')."',
                stage = {$_SESSION["stage"]}
            WHERE
                email = '{$email}'
                
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не обнов. поле last_dt для \"{$email}\"");
        }
    }

    function getUserData($email){
        global $link;

        $result = $link->query("
            SELECT
                user_id, nick, passhash, role, stage, room_id
            FROM
                users
            WHERE
                email = '{$email}'
            LIMIT 1
        ");
        // Если результат запроса провалился
        if (!$link->isMysqliResultValid($result)){
            throw new Brake("Не найден пользователь \"{$email}\"");
        }
        return $result->fetch_assoc();
    }

    function chaoticLogin($email, $pass){
        global $link, $psCount;

        if ($psCount >= 40){
            throw new Brake("Сервера перегружены, повторите попытку позже", 0, LOG_LVL_ERR);
        }

        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($email === False){
            throw new Brake("Некорректный формат email");
        }
        
        $output = array();
        if (isset($_SESSION["email"])){
            $output["msg"]      = "Вы уже вошли в систему";
        }
        session_unset(); 
        $data = getUserData($email);
        // Проверяем совпадения пароля с хешом из БД
        if (password_verify($pass, $data["passhash"])){
            // Признак авторизации -- наличие аттрибута "email" в супер.глоб. массиве $_SESSION
            $_SESSION["email"]   =  $output["email"]  = $email;
            $_SESSION["role"]    =  $output["role"]   = $data["role"];
            $_SESSION["stage"]   =  $output["stage"]  = $data["stage"];
            $_SESSION["nick"]    =  $output["nick"]   = $data["nick"];
            $_SESSION["user_id"] =  $output["user_id"]= $data["user_id"];
            $output["room_id"]   =  $data["room_id"];
            
            Log::msg("Аутентифицирован \"{$data["nick"]}\"");
            updateStageInMenu($email);
        }
        else {
            // throw new Brake("Введен неверный пароль пользователя \"{$email}\"");
            throw new Brake("Ошибка входа");
        }
        return $output;
    }

    //TODO: ИЗМЕНИТЬ МЕХАНИЗМ ВЫХОДА ИЗ АКК.
    // МЕНЯТЬ room_id и stage ???
    function chaoticLogout(){
        global $link;

        if (!isset($_SESSION["email"])){
            throw new Brake("Вы не вошли в систему");
        }
        $result = $link->query("
            UPDATE
                users
            SET
                last_dt = '".date('Y-m-d H:i:s')."',
                stage = IF(stage = ".USTAGE_INMENU.", ".USTAGE_INAUTH.", stage)
            WHERE
                email = '{$_SESSION["email"]}'
        ");
        if (!$link->isQueryResultValid($result)){
            Log::warning("Не обнов. поле stage=USTAGE_INAUTH для пользователя \"{$_SESSION["email"]}\"");
        }
        Log::msg("Пользователь \"{$_SESSION["email"]}\" вышел");
        session_unset();
    }

    function chaoticSignup($email, $pass, $nick){
        global $link, $psCount;

        if ($psCount >= 40){
            throw new Brake("Сервера перегружены, повторите попытку позже", 0, LOG_LVL_ERR);
        }
        
        if (isset($_SESSION["email"])){
            throw new Brake("Вы уже вошли в систему");
        }
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($email === False){
            throw new Brake("Некорректный формат email");
        }
        if ($nick[0] == "#"){
            throw new Brake("Недопустимый символ \"#\" в никнейме");
        }
        $passhash = password_hash($pass, PASSWORD_DEFAULT);
        
        // Добавление пользователя
        $result = $link->query("
            INSERT INTO
                users (email, passhash, nick, stage)
            VALUES 
                ('{$email}', '{$passhash}', '{$nick}', ".USTAGE_INMENU.")
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка регистрации");
        }
        $userID = $link->mysqli->insert_id;

        $output = array();
        try {
            // УСТАНОВКА ПОЛЬЗОВ. НАСТРОЕК ПО УМОЛЧ.
            $result = $link->query("
                INSERT INTO
                    users_settings(user_id)
                VALUES
                    ({$userID})
            ");
            if (!$link->isQueryResultValid($result)){
                throw new Brake("Ошибка первоначальной настройки", 1);
            }
            // УСТАНОВКА ПОЛЬЗОВ. РЕЙТИНГА ПО УМОЛЧ.
            $result = $link->query("
                INSERT INTO
                    ratings(user_id)
                VALUES
                    ({$userID})
            ");
            if (!$link->isQueryResultValid($result)){
                throw new Brake("Ошибка первоначальной настройки", 1);
            }
            $_SESSION["email"]   = $output["email"]   = $email;
            $_SESSION["nick"]    = $output["nick"]    = $nick;
            $_SESSION["user_id"] = $output["user_id"] = $userID;
            $_SESSION["role"]    = $output["role"]    = 0;
            $_SESSION["stage"]   = $output["stage"]   = USTAGE_INMENU;
            $output["room_id"]   = null;
            updateStageInMenu($email);
        }
        catch (Brake $e){
            // Требуется откат изменений
            if ($e->getCode() == 1){
                $result = $link->query("
                    DELETE FROM
                        users
                    WHERE 
                        user_id = {$userID}
                ");
                if (!$link->isQueryResultValid($result)){
                    Log::error("Ошибка удаления невалидного пользователя \"{$email}\"");
                }
            }
            throw $e;
        }
        return $output;
    }

?>