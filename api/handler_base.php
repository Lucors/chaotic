<?php
    if (!isset($request["op"])){
        throw new Brake("Не задан параметр OPERATION (op)");
    }
    $response["result"] = True;
?>