<?php
    // CHAOTIC API

    // TODO: Удалить отладочное поведение при ошибке PHP
    ini_set("error_reporting", E_ALL);
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);

    if (!isset($_SESSION)){
        session_start();
    }
    require_once("connect.php");
    // require_once("log.php");
    // require_once("general.php");

    // Переменная ответа на запрос
    $response = array(
        "result" => False
    ); 

    try {
        // Получение метода общения с клиентом
        $method = General::getInputMethod();
        if ($method == INPUT_POST){
            $rawRequest = file_get_contents('php://input');
            $request = json_decode(urldecode($rawRequest), True);
        }
        else {
            $request =& $_GET;
        }

        if (is_null($request)){
            throw new Brake("Пустой запрос");
        }
        if (!isset($request["route"])){
            throw new Brake("Не определен путь запроса (route)"); 
        }

        // Проверяем что пользователь авторизован 
        if (($request["route"] != "auth" && $request["route"] != "debug") &&
            (!isset($_SESSION["email"]) || !isset($_SESSION["user_id"]))){
            throw new Brake("Пользователь не авторизирован", -1);
        }
        // Подключение к БД (connect.php -> mysqli) 
        $link = new Connection(General::$dbHost, General::$dbUser, General::$dbPass, General::$dbName);
        $link->connect();
        if (is_null($link->mysqli)){
            throw new Brake("Ошибка подключения к БД");
        }

        // Подключаем обработчик по запрашиваемому маршруту
        $routeHandler = "{$request['route']}/handler.php";
        if (!file_exists($routeHandler)){
            throw new Brake("Неверный путь запроса (route)");
        }
        require_once($routeHandler);
        $link->disconnect();
    }
    catch (Brake $b){
        if($b->loglvl != LOG_LVL_NONE){
            Log::auto($b, $b->loglvl);
            // Log::auto($e->getMessage(), $e->getCode());
        }
        $response["result"] = False;
        $response["msg"]    = $b->getMessage();
        $response["code"]   = $b->getCode();
    }
    catch (Exception $e){
        $response["result"] = False;
        $response["msg"]    = "[НЕВЕРНЫЙ ОБРАБОТЧИК ИСКЛЮЧЕНИЯ]".$b->getMessage();
    }

    // header('Access-Control-Allow-Origin: *');
    // header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    // header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, x-requested-with');
    header('Content-type: application/json');
    echo json_encode($response);
?>