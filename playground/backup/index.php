<?php
    // Уберите эти строчки перед выкатом 
    ini_set("error_reporting", E_ALL);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);

    if (!isset($_SESSION)){
        session_start();
    }
    require_once("log.php");
    require_once("general.php");
    require_once("connect.php");

    $lang = filter_input(INPUT_GET, "lang");
    if (array_key_exists($lang, General::$allowedLangs)){
        $filePostfix = General::$allowedLangs[$lang];
    }
    else {
        $filePostfix = "";
    }

    // ТУТ ПРОВЕРИТЬ ЧТО ЧЕЛ НЕ В ИГРЕ УЖЕ
    // ИНАЧЕ SET GAMESTAGE = 0 OR 1

    // Проверяем пользователя
    $link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
    $link->connect();

    // currentStage:
    // -- 0: Авторизация
    // -- 1: В меню
    // -- 2: В комнате
    // -- 3: В игре (template_game.php)

    $_SESSION["currentStage"] = 0;
    if (isset($_SESSION["email"])){
        $_SESSION["currentStage"] = 1;

        $result = $link->query("
            SELECT
                users.user_id, nick, users_roles.role, rooms.stage
            FROM
                users
            LEFT JOIN
                users_roles
            ON
                users.user_id = users_roles.user_id
            LEFT JOIN
                rooms
            ON
                users.room_id = rooms.room_id 
            WHERE
                email = '{$_SESSION["email"]}'
            LIMIT 1
        ");

        // Обновляем данные сессии
        if ($link->isMysqliResultValid($result)){
            $data = $result->fetch_assoc();

            $_SESSION["nick"] = $data["nick"];
            $_SESSION["user_id"] = $data["user_id"];
            if (is_null($data["role"])){
                $data["role"] = 0;
            }
            $_SESSION["role"] = $data["role"];

            if (!is_null($data["stage"])){
                $_SESSION["currentStage"] = 2;
                if ($data["stage"] > 0){
                    $_SESSION["currentStage"] = 3;
                }
            }
        }
        // Или же убиваем невалидные данные
        else {
            session_unset();
            $_SESSION["currentStage"] = 0;
        }
    }
    $link->disconnect();
?>

<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title>Chaotic!</title>

        <link rel="shortcut icon" href="assets/img/favicon.png"/>

        <link rel = "stylesheet" href = "assets/css/vars.css">
        <link rel = "stylesheet" href = "assets/css/interact.css">
        <link rel = "stylesheet" href = "assets/css/general.css">

        <script src="assets/js/lib/jquery.min.js"></script>
        <!-- <script src="assets/js/lib/velocity.min.js"></script> -->
        <script src="assets/js/lib/anime.min.js"></script>
        <script src="assets/js/general.js"></script>

        <?php
            if (isset($_SESSION["currentStage"]) && $_SESSION["currentStage"] == 3){
                    echo "<link rel = 'stylesheet' href = 'assets/css/game.css'>";
                    echo "<script src='assets/js/game.js'></script>";
            }
            else {
                echo "<link rel = 'stylesheet' href = 'assets/css/intro.css'>";
                echo "<link rel = 'stylesheet' href = 'assets/css/menu.css'>";
                echo "<link rel = 'stylesheet' href = 'assets/css/room.css'>";
                echo "<script src='assets/js/intro.js'></script>";
                echo "<script src='assets/js/menu.js'></script>";
                echo "<script src='assets/js/room.js'></script>";
            }
        ?>
    </head>

    <body>
        <?php
            require("template_service{$filePostfix}.php"); 
            
            if (isset($_SESSION["currentStage"]) && $_SESSION["currentStage"] == 3){
                echo "<div id='game' class='panel'>";
                require("template_game{$filePostfix}.php"); 
                echo "</div>";
            }
            else {
                echo "<div id='room' class='panel'>";
                require("template_room{$filePostfix}.php"); 
                echo "</div>";

                echo "<div id='menu' class='panel'>";
                require("template_menu{$filePostfix}.php"); 
                echo "</div>";

                echo "<div id='intro' class='panel'>";
                require("template_intro{$filePostfix}.php"); 
                echo "</div>";
            }
        ?>

        <script defer type="text/javascript">
            <?php
                // Имеет смысл при генерации страницы сразу передать некоторые значения
                if (isset($_SESSION["email"])){
                    echo "window.currentStage = {$_SESSION['currentStage']};";
                    echo "window.user.nick = \"{$_SESSION['nick']}\";";
                    echo "window.user.user_id = \"{$_SESSION['user_id']}\";";
                    echo "window.user.role = \"{$_SESSION['role']}\";";
                }
            ?>
        </script>
    </body>
</html>