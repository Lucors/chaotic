<?php
    // QUESTIONS REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");

    switch ($request["op"]) {
        case "get_unique":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            $response["question"]  = getUniqueQuestion($request["room_id"]);
            break;
        case "answer_test":
            if (!isset($request["qid"])){
                throw new Brake("Не задан параметр QUESTION_ID (qid)");
            }
            if (!isset($request["answer"])){
                throw new Brake("Не задан параметр ANSWER (answer)");
            }
            $response["answer"] = checkAnswer($request["qid"], $request["answer"]);
            break;
        case "answer":
            if (!isset($request["room_id"])){
                throw new Brake("Не задан параметр ROOM_ID (room_id)");
            }
            if (!isset($request["qid"])){
                throw new Brake("Не задан параметр QUESTION_ID (qid)");
            }
            if (!isset($request["give_up"])){
                throw new Brake("Не задан параметр GIVE_UP (give_up)");
            }
            if (!array_key_exists("answer", $request)){
                throw new Brake("Не задан параметр ANSWER (answer)");
            }
            $response["answer"] = answerQuestion($request["room_id"], $request["qid"], 
                                                    $request["answer"], $request["give_up"]);
            break;
        default:
            $response["result"] = False;
            break;
    }
?>