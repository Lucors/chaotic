<?php
    // AUTH REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    
    switch ($request["op"]) {
        case "login":
            if (!isset($request["email"]) || !isset($request["pass"])){
                throw new Brake("Введите email и пароль");
            }
            $response["user_data"] = chaoticLogin(
                $request["email"],
                $request["pass"]
            );
            if (key_exists("msg", $response["user_data"])){
                $response["msg"] = $response["user_data"]["msg"];
                unset($response["user_data"]["msg"]);
            }
            break;
        case "signup":
            if (!isset($request["email"]) 
                || !isset($request["pass"]) 
                || !isset($request["nick"])){
                throw new Brake("Введите email, пароль и nickname");
            }
            $response["user_data"] = chaoticSignup(
                $request["email"],
                $request["pass"],
                $request["nick"]
            );
            break;
        case "logout":
            chaoticLogout();
            break;
        default:
            $response["result"] = False;
            break;
    }
?>