<?php
	// Проверяет изменения элемента [$key] по отношению к предыдущему ответу
	function applyResponseChanges($key){
		global $response, $lastResponse;

		if ($response[$key] == $lastResponse[$key]){
			unset($response[$key]);
			return false;
		}
		$lastResponse[$key] = $response[$key];
		return true;
	}

	// Обновляет $roomData, возвращает $output[] с code => SYNC_ROOM_* 
	function roomSelector(){
		global $link, $roomData, $sleepTime, $response;
		$output = array("code" => SYNC_COMMON_ERROR);

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
				rooms.room_id = {$roomData['room_id']}
		");
		if ($link->isMysqliResultValid($result)){
			$allReady = True;
			$tmpUserList = array();
			while ($data = $result->fetch_assoc()) {
				$roomData["stage"] = $data["rstage"];
				unset($data["rstage"]);
				$tmpUserList[] = $data;
				if ($data["ustage"] != USTAGE_INGAME_READY){
					$allReady = false;
				}
			}
			$output["stage"] = $roomData["stage"];
			if ($tmpUserList != $roomData["user_list"]){
				$roomData["user_list"] 	= $tmpUserList;
				$output["ulist"] 		= $tmpUserList;
				$output["code"] 		= SYNC_ROOM_PLAYER_LIST;
				return $output;
			}
			
			if ($output["stage"] == RSTAGE_GAME_START){
				if (isset($response["room"]["code"])){
					if ($response["room"]["code"] == SYNC_ROOM_PLAYERS_READY){
						// $sleepTime = 10; //НЕ РАБОТАЕТ 
					}
				}
				$output["code"] = SYNC_ROOM_OK;
			}
			elseif ($output["stage"] == RSTAGE_GAME_END) {
				$output["code"] = SYNC_ROOM_FINISH;
				$result = $link->query("
					SELECT
						users.user_id as uid,  users.nick
					FROM
						ingame_position
					INNER JOIN 
						users
					ON
						ingame_position.user_id = users.user_id
					WHERE
						ingame_position.position = 'finish'
				");
				if ($link->isMysqliResultValid($result)){
					$output["winner"] = $result->fetch_assoc();
					$output["winner"]["avatar"] = General::getCorrectAvatarPath($output["winner"]["uid"]);
				}
			}
			else {
				if ($allReady){
					$output["code"] = SYNC_ROOM_PLAYERS_READY;
				}
				else {
					$output["code"] = SYNC_ROOM_PLAYERS_NREADY;
				}
			}
		}	
		else {
			$output["code"] = SYNC_ROOM_DEAD;
		}

		return $output;
	}

	// Обновляет $stepData, возвращает $output[] с code => SYNC_STEP_* 
	function stepSelector(){
		global $link, $roomData, $stepData;
		$output = array("code" => SYNC_COMMON_ERROR);

		$result = $link->query("
			SELECT
				user_id as current_user_id,
				order_value, start_dt, stage, step_number
			FROM
				ingame_steps
			WHERE
				ingame_steps.room_id = {$roomData['room_id']}
		");
		if ($link->isMysqliResultValid($result)){
			$tmpData = $result->fetch_assoc();
			
			// Если игрок отвечает или перемещается
			if ($tmpData["stage"] == SSTAGE_ANSWERING || $tmpData["stage"] == SSTAGE_MOVE){
				// Если задано время начала
				$output["code"] = SYNC_STEP_PING;
				if (!is_null($tmpData["start_dt"])){
					$stepData["ping"] = strtotime("now") - strtotime($tmpData["start_dt"]);
					// Время кончилось
					if ($stepData["ping"] > $roomData["step_time"]){
						$stepData["ping"] = $roomData["step_time"];
						$output["code"]	  = SYNC_STEP_TIMEOUT;
						$output["uid"] 	= $stepData["current_user_id"];
					}
					$output["ping"]	= $stepData["ping"];
				}
				// else {
				// 	$output["code"] = SYNC_STEP_TIMEOUT;
				// 	$output["ping"]	= $stepData["step_time"];
				// }
			}


			// Новый шаг
			if ( ($stepData["current_user_id"] != $tmpData["current_user_id"])
				|| ($stepData["stage"] != $tmpData["stage"]) ){
			// 	$output["code"] 	= SYNC_STEP_STAGE + SSTAGE_WAIT;
			// 	$output["uid"] 		= $tmpData["current_user_id"];
			// 	$output["ordval"] 	= $tmpData["order_value"];
			// }
			// else
			// if ($stepData["stage"] != $tmpData["stage"]) {
				$output["code"] = SYNC_STEP_STAGE + $tmpData["stage"];
				switch ($tmpData["stage"]) {
					case SSTAGE_START:
						// Необходим вызов StepOrderPush [CLIENT]
						break;
					case SSTAGE_ORDER: 	
					case SSTAGE_ANSWERING:
						$output["ordval"] = $tmpData["order_value"];
						$output["uid"] 	  = $tmpData["current_user_id"];
						break;
					case SSTAGE_YES:
						$output["uid"] 	  = $tmpData["current_user_id"];
						break;
					case SSTAGE_NO:
						$output["uid"] 	  = $tmpData["current_user_id"];
						break;
					// case SSTAGE_MOVING:
					// 	$output["uid"] 	  = $tmpData["current_user_id"];
					// 	break;
					case SSTAGE_MOVE_END:
						$output["uid"] 	  = $tmpData["current_user_id"];
						break;
					case SSTAGE_QUESTION_OVERFLOW:
						$output["uid"] 	  = $tmpData["current_user_id"];
						break;
					default:
						$output["code"] = SYNC_COMMON_ERROR;
						break;
				}
			}

			$stepData = array_merge($stepData, $tmpData);
		}

		return $output;
	}

	// Обновляет $positionData, возвращает $output[] с code => SYNC_POS_* 
	function positionSelector($finish=false){
		global $link, $roomData, $stepData, $positionData;
		$output = array("code" => SYNC_COMMON_ERROR);

		$query = "
			SELECT
				user_id as uid, position, dice
			FROM
				ingame_position
			WHERE
				ingame_position.room_id = {$roomData['room_id']}
		";
		if ($finish){
			$result = $link->query($query);
			if ($link->isMysqliResultValid($result)){
				$output["code"] = SYNC_POS_DATA;
				while ($tmpData = $result->fetch_assoc()) {
					$output["movements"][] = $tmpData;
					$positionData["movements"][] = $tmpData; 
				}
			}
		}
		else {
			$query .= " AND ingame_position.user_id = {$stepData['current_user_id']}";
			$result = $link->query($query);
			if ($link->isMysqliResultValid($result)){
				$output["code"] 							= SYNC_POS_MOVE;
				$tmpData 									= $result->fetch_assoc();
				$output["movement"] 						= $tmpData;
				$positionData["movement"] = $tmpData;
			}
		}

		return $output;
	}
?>