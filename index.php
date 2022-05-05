<?php
    // Уберите эти строчки перед выкатом 
    ini_set("error_reporting", E_ALL);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);

    if (!isset($_SESSION)){
        session_start();
    }
    // connect подтягивает log, а тот в свою очередь general
    require_once("api/connect.php");
    // require_once("api/log.php");
    // require_once("api/general.php");
    // require_once("api/auth/methods.php");

    $lang = filter_input(INPUT_GET, "lang");
    $filePostfix = "";
    if (array_key_exists($lang, General::$allowedLangs)){
        $filePostfix = General::$allowedLangs[$lang];
    }

    // Проверяем пользователя
    $link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
    $link->connect();

    $psList = shell_exec("ps -A");
    $psCount = count(preg_split("/\n/", $psList)) -2;

    $room_id    = -1;
    // $creator_id = -1;
    $_SESSION["stage"] = USTAGE_INAUTH;
    if (isset($_SESSION["email"]) && $psCount < 40){
        $result = $link->query("
            SELECT
                user_id, nick, role, stage, room_id
            FROM
                users
            WHERE
                email = '{$_SESSION["email"]}'
            LIMIT 1
        ");

        // Обновляем данные сессии
        if ($link->isMysqliResultValid($result)){
            $data = $result->fetch_assoc();
            $_SESSION["nick"]     = $data["nick"];
            $_SESSION["user_id"]  = $data["user_id"];
            $_SESSION["role"]     = $data["role"];
            if (!is_null($data["room_id"])){
                $room_id          = $data["room_id"];
            }
            
            if ($data["stage"] == USTAGE_INAUTH){
                $data["stage"] = USTAGE_INMENU;
            }
            $result = $link->query("
                UPDATE
                    users
                SET
                    last_dt = '".date('Y-m-d H:i:s')."',
                    stage = {$data["stage"]}
                WHERE
                    email = '{$_SESSION["email"]}'
            ");
            if (!$link->isQueryResultValid($result)){
                Log::warning("Не обнов. поле last_dt для пользователя \"{$_SESSION["email"]}\"");
            }
            $_SESSION["stage"] = $data["stage"];

            // if ($room_id != -1){
            //     $result = $link->query("
            //         SELECT
            //             creator_id
            //         FROM
            //             rooms
            //         WHERE
            //             room_id = {$room_id}
            //         LIMIT 1
            //     ");

            //     // Обновляем данные сессии
            //     if ($link->isMysqliResultValid($result)){
            //         $data = $result->fetch_assoc();
            //         $creator_id = $data["creator_id"];
            //     }
            // }
        }
        // Или же убиваем невалидные данные
        else {
            session_unset();
        }
    }
    $link->disconnect();
?>

<html>
    <head>
        <title>Chaotic!</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="description" content="Браузерная онлайн-игра Chaotic!">
        <meta name="keywords" content="игра онлайн-игра chaotic chaoticgame">

        <link rel="shortcut icon" href="assets/img/favicon.png"/>
        <link rel = "stylesheet" href = "assets/css/vars.css">
        <link rel = "stylesheet" href = "assets/css/interact.css">
        <link rel = "stylesheet" href = "assets/css/general.css">

        <script src="assets/js/lib/jquery.min.js"></script>
        <script src="assets/js/lib/anime.min.js"></script>
        <script src="assets/js/general.js"></script>

        <?php
            if (isset($_SESSION["stage"]) && $_SESSION["stage"] >= USTAGE_INGAME_LOADING){
                    echo "<link rel = 'stylesheet' href = 'assets/css/game.css'>";
                    echo "<script src='assets/js/lib/jcanvas.min.js'></script>";
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

        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-GE6081ZSHN"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', 'G-GE6081ZSHN');
        </script>
    </head>

    <body class="initial">
        <?php
            if ($psCount >= 40){
                echo "<div id='overloaded'>Сервера перегружены, повторите попытку позже</div>";
            }
            require("template_general{$filePostfix}.php"); 
            
            if (isset($_SESSION["stage"]) && $_SESSION["stage"] >= USTAGE_INGAME_LOADING){
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
                    echo "window.user.nick = \"{$_SESSION['nick']}\";";
                    echo "window.user.user_id = {$_SESSION['user_id']};";
                    echo "window.user.role = {$_SESSION['role']};";
                    echo "window.user.stage = {$_SESSION['stage']};";
                    // echo "window.user.settings_data = {$_SESSION['settings_data']};";
                    echo "window.currentRoom.id = {$room_id};";
                }
            ?>
        </script>
    </body>
</html>