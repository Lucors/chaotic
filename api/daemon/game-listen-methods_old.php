<?php
	function gameState($rid){
		global $link;

		$output = array();
		$result = $link->query("
			SELECT
				rooms.stage as rstage, users.user_id as uid, users.stage as ustage
			FROM
				rooms
			INNER JOIN 
				users
			ON
				rooms.room_id = users.room_id
			WHERE
				rooms.room_id = {$rid}
		");
		if ($link->isMysqliResultValid($result)){
			$output["ulist"] = array();
			$allReady = True;
			while ($data = $result->fetch_assoc()) {
				$output["stage"] 	= $data["rstage"];
				unset($data["rstage"]);
				$output["ulist"][]	= $data;

				if ($data["ustage"] != USTAGE_INGAME_READY){
					$allReady = false;
				}
			}
			
			if ($output["stage"] == RSTAGE_GAME_START){
				$output["code"] = GCODE_SYNC_STAGE;
			}
			else {
				if ($allReady){
					$output["code"] = GCODE_PLAYERS_READY;
				}
			}
		}
		else {
			$output["code"] = GCODE_DEAD_ROOM;
		}
		return $output;
	}


	function pingSelector($rid){
		global $link;
		
		$output = array();
		$result = $link->query("
			SELECT
				start_dt, step_time, ingame_steps.stage
			FROM
				rooms
			INNER JOIN 
				ingame_steps
			ON
				rooms.room_id = ingame_steps.room_id
			WHERE
				rooms.room_id = {$rid}
		");
		if ($link->isMysqliResultValid($result)){
			$data = $result->fetch_assoc();
			$output["code"] = GCODE_PING_WAIT;
			$output["ping"] = 0;
			if (!is_null($data["start_dt"])){
				$output["ping"] = strtotime("now") - strtotime($data["start_dt"]);
				if ($output["ping"] > $data["step_time"]){
					$output["ping"] = $data["step_time"];
					$output["code"] = GCODE_PING_TIMEOUT;
				}

				$output["limit"] = false;
				if ($output["ping"] >= $data["step_time"]){
					$output["limit"] = true;
				}

				// $output["ping"] = (int)$data["step_time"] +  $output["ping"];
				// $output["ping"] = $data["step_time"] - 
			// 	$output["ping"] = $data["step_time"] + (strtotime($date["start_dt"]) - time());
			}
		}
		return $output;
	}


	function orderPusher($rid){
		global $link;

		$output = array("code"=>GCODE_STEP_QUEST, "uid"=>null);
		$result = $link->query("
			SELECT
				user_id as uid, ingame_steps_order.value as order_value
			FROM
				ingame_steps_order
			WHERE
				ingame_steps_order.room_id = {$rid}
			ORDER BY
				ingame_steps_order.value
		");
		$orders = array();
		while($data=$result->fetch_assoc()){
			$orders[$data["order_value"]] = $data["uid"];
		}

		$result = $link->query("
			SELECT
				order_value
			FROM
				ingame_steps
			WHERE
				ingame_steps.room_id = {$rid}
		");
		$lastOrderValue = $result->fetch_assoc()["order_value"];
		if (is_null($lastOrderValue)){
			$lastOrderValue = 0;
		}

		$query = "
			UPDATE
				ingame_steps
			SET
		";

		$orderKeys = array_keys($orders);

		$output["uid"] = $orders[$orderKeys[0]];
		$tmpQuery = "user_id = {$orders[$orderKeys[0]]}, order_value = {$orderKeys[0]}";
		foreach ($orders as $i => $value) {
			if ($i > $lastOrderValue){
				$output["uid"] = $value;
				$tmpQuery = "user_id = {$value}, order_value = {$i}";
				break;
			}
		}
		$query .= $tmpQuery;

		// if (end($orders)["order_value"] <= $lastOrderValue){
		// 	$output["uid"] = $orders[0]['uid'];
		// 	$query .= "user_id = {$orders[0]['uid']}, order_value = {$orders[0]['order_value']}";
		// }
		// else {
		// 	$output["uid"] = $orders[$lastOrderValue]['uid'];
		// 	$query .= "user_id = {$orders[$lastOrderValue]['uid']}, order_value = {$orders[$lastOrderValue]['order_value']}";
		// }
        $dt = date("Y-m-d H:i:s", strtotime("now"));
		$query .= " , start_dt = '{$dt}' WHERE ingame_steps.room_id = {$rid}";
		// Log::warning($query);
		$result = $link->query($query);
		return $output;
	}


	function orderSelector($rid){
		global $link;

		$output = array("code"=>GCODE_STEP_QUEST, "uid"=>null);
		$result = $link->query("
			SELECT
				user_id as uid, order_value
			FROM
				ingame_steps
			WHERE
				ingame_steps.room_id = {$rid}
		");
		$data = $result->fetch_assoc();
		$output["uid"] = $data["uid"];
		$output["ordval"] = $data["order_value"];
		return $output;
	}
?>