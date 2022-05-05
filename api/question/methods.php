<?php
    // QUESTIONS METHODS

    function getUniqueQuestion($roomID){
        global $link;

        $result = $link->query("
            SELECT
                ignore_question
            FROM
                ingame_steps_order
            WHERE 
                user_id = {$_SESSION['user_id']}
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения списка пропуска");
        }
        $ignoreList = $result->fetch_assoc()["ignore_question"];
        $ignoreFilter = "";
        if (!empty($ignoreList)){
            $ignoreFilter = substr($ignoreList, 0, -1); // Обрезаем запятую
            $ignoreFilter = "question_id NOT IN ({$ignoreFilter}) AND ";
        }

        $result = $link->query("
            SELECT
                topic_id
            FROM
                rooms_topics
            WHERE 
                room_id = {$roomID}
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения списка тем");
        }
        $topics = "";
        while($data = $result->fetch_assoc()){
            $topics .= "{$data['topic_id']},";
        }
        $topics = substr($topics, 0, -1); // Обрезаем запятую

        $result = $link->query("
            SELECT
                question_id as qid, topic_id as tid, 
                type, value, 
                correct_answer as cans,
                incorrect_answer_1 as ians1,
                incorrect_answer_2 as ians2,
                incorrect_answer_3 as ians3
            FROM
                questions
            WHERE
                {$ignoreFilter}
                topic_id IN ({$topics})
            ORDER BY RAND()
            LIMIT 1;
        ");
        if (!$link->isMysqliResult($result)){
            $link->query("
                UPDATE
                    ingame_steps
                SET
                    stage = ".SSTAGE_QUESTION_OVERFLOW."
                WHERE 
                    ingame_steps.room_id = {$roomID}
            ");
            throw new Brake("Ошибка получения вопроса");
        }
        $output = $result->fetch_assoc();

        //Возвращаем ответы в случайном порядке
        $answers = array($output["cans"], $output["ians1"], $output["ians2"], $output["ians3"]);
        shuffle($answers);
        $output["answers"] = $answers;
        unset($output["cans"]);
        unset($output["ians1"]);
        unset($output["ians2"]);
        unset($output["ians3"]);

        $ignoreList .= "{$output['qid']},";
        $result = $link->query("
            UPDATE
                ingame_steps_order
            SET
                ignore_question = '{$ignoreList}'
            WHERE 
                user_id = {$_SESSION['user_id']}
        ");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка обновления списка пропуска");
        }

        $dt = date("Y-m-d H:i:s", strtotime("now"));
        $result = $link->query("
			UPDATE
				ingame_steps
			SET
                stage = ".SSTAGE_ANSWERING.",
                start_dt = '{$dt}'
            WHERE 
                ingame_steps.room_id = {$roomID}

		");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка обновления игрового шага");
        }

        return $output;
    }

    function checkAnswer($qid, $answer){
        global $link;
        $result = $link->query("
            SELECT
                question_id as qid,
                correct_answer as cans
            FROM
                questions
            WHERE
                question_id = {$qid}
        ");
        if (!$link->isMysqliResult($result)){
            throw new Brake("Ошибка получения вопроса");
        }
        //Проверка ответа $answer при помощи хэш-функции
        if (md5($answer) == md5($result->fetch_assoc()["cans"])){
            return true;
        }
        return false;
    }

    function answerQuestion($roomID, $qid, $answer, $giveUP){
        global $link;
        
        // $output       = array();
        $answerValid = false;
        if ($giveUP == "false"){
            $answerValid  = checkAnswer($qid, $answer);
        }
        $newStepStage = SSTAGE_NO;
        if ($answerValid) {
            $newStepStage = SSTAGE_YES;
        }

        $result = $link->query("
			UPDATE
				ingame_steps
			SET
                stage = {$newStepStage}
            WHERE 
                ingame_steps.room_id = {$roomID}
                AND
                ingame_steps.user_id = {$_SESSION['user_id']}

		");
        if (!$link->isQueryResultValid($result)){
            throw new Brake("Ошибка ответа на вопрос");
        }

        return $answerValid;
    }
?>