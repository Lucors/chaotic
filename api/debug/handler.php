<?php
    // DEBUG REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    
    switch ($request["op"]) {
        case "time":
            $response["time"] = date("Y-m-d H:i:s");
            break;
        case "echo":
            $response["request"] = $request;
            break;
        default:
            $response["result"] = False;
            break;
    }
?>