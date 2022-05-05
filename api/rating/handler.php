<?php
    // RATING REQUEST HANDLER
    require_once("handler_base.php");
    require_once("methods.php");
    
    switch ($request["op"]) {
        case "getall":
            $rating = getAllRating($_SESSION["user_id"]);
            $response["rating_list"] = $rating["list"];
            if (key_exists("user_rating", $rating)){
                $response["user_rating"] = $rating["user_rating"];
            }
            break;
        default:
            $response["result"] = False;
            break;
    }
?>