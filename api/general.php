<?php
	// Уровни логирования
	define("LOG_LVL_NONE", 	0); //Не логировать
	define("LOG_LVL_ERR", 	1); //Логировать ошибки
	define("LOG_LVL_WARN", 	2); //Логировать предупреждения и ниже
	define("LOG_LVL_MSG", 	3); //Логировать сообщения и ниже
	define("LOG_LVL_DEBUG", 4); //Логировать всё

	// Состояния игрока 
	define("USTAGE_INAUTH", 			0); //Не авторизирован
	define("USTAGE_INMENU", 			1); //В главном меню
	// define("USTAGE_INROOM_LOADING", 	2); //Загружается в лобби
	define("USTAGE_INROOM_NOTREADY", 	3); //В лобби, не готов
	define("USTAGE_INROOM_READY", 		4); //В лобби, готов или создатель лобби
	define("USTAGE_INGAME_LOADING", 	5); //В игре, ожидание игроков
	// define("USTAGE_INGAME_NOTREADY", 6); //В игре, не готов ()
	define("USTAGE_INGAME_READY", 		7); //В игре, готов
	define("USTAGE_INGAME_NOTREADY", 	8); //В игре, не готов (Анимация?)

	// Состояния игровой комнаты 
	define("RSTAGE_LOBBY_WAIT", 		0); //Лобби, ожидают игроков
	define("RSTAGE_GAME_WAIT", 			1); //Переход в игру, ожидание игроков
	// define("RSTAGE_GAME_QUEUE", 		2); //Распределение ходов
	define("RSTAGE_GAME_START", 		3); //Игра началась
	define("RSTAGE_GAME_END", 			4); //Конец игры

	// Ответы демона игры
	// define("GCODE_ERROR_PHP", 	   -1);
	// define("GCODE_NONE", 			0);
	// define("GCODE_DEAD_ROOM",		1);
	// define("GCODE_PLAYERS_READY",	2);
	// define("GCODE_SYNC_STAGE",		3);
	// define("GCODE_STEP_QUEST",		4);
	// define("GCODE_PING_WAIT",		5);
	// define("GCODE_PING_TIMEOUT",	6);
	// define("GCODE_PING_WRONG",		7);
	// define("GCODE_PING_CORRECT",	8);

	// define("GCODE_STEP_FORK",		-7);
	// define("GCODE_STEP_MOVE",		-8);
	// define("GCODE_FINISH",			-9);

	// game-listen.php SYNC CODES
	define("SYNC_COMMON_ERROR", 				0);	// Общий. Ошибка
	define("SYNC_COMMON_DATA", 					1);	// Общий. Обнови данные
	// ROOM SYNC
	define("SYNC_ROOM_DEAD", 				2);	// Комната утеряна
	define("SYNC_ROOM_PLAYERS_NREADY", 		3);	// Игроки не готовы
	define("SYNC_ROOM_PLAYERS_READY", 		4);	// Все игроки подключены
	define("SYNC_ROOM_PLAYER_LIST", 		5);	// Список игроков изменился
	define("SYNC_ROOM_OK", 					6);	// Игра идет
	define("SYNC_ROOM_FINISH", 				7);	// Список игроков изменился
	// STEP SYNC
	define("SYNC_STEP_PING", 				2);	// Обновление счетчика
	define("SYNC_STEP_TIMEOUT", 			3);	// Время вышло
	define("SYNC_STEP_STAGE", 				4);	// Синхронизировать состояние шага
		// Состояния шага
		// SYNC_CODE = SYNC_STEP_STAGE + SSTAGE_*
		define("SSTAGE_START", 				1); // Начало игры (?)
		define("SSTAGE_ORDER", 				2); // Запроси вопрос
		define("SSTAGE_ANSWERING", 			3); // Ожидание ответа
		define("SSTAGE_YES", 				4); // Отвечен, верно
		define("SSTAGE_NO", 				5); // Отвечен, неверно
		// define("SSTAGE_MOVING", 			6); // Перемещение
		define("SSTAGE_MOVE_END", 			7); // Перемещение окончено
		define("SSTAGE_QUESTION_OVERFLOW", 	8); // Превышено кол-во unique вопросов
	// POSITION SYNC
	define("SYNC_POS_MOVE", 				2);	// Сместить игрока в ячейку
	define("SYNC_POS_DATA", 				3);	// Синхронизировать позиции игроков


	// NODES TYPES
	define("NODE_START", 		1);
	define("NODE_STEP", 		2);
	define("NODE_FINISH", 		3);
	define("NODE_TP", 			4);
	define("NODE_PRISON", 		5);
	define("NODE_FORK", 		6);


    class General {
    	public static $allowLogging = true; //Разрешить вести лог
		public static $loggingPath = "/assets/log/"; //Путь к логам
		public static $loggingLevel = LOG_LVL_MSG; //Уровень логирования

		// Данные соединения с БД по умолчанию
		public static $dbHost = ""; //SECRET
		public static $dbUser = ""; //SECRET
		public static $dbPass = ""; //SECRET
		public static $dbName = ""; //SECRET

		// Доступные языки (Язык => Префикс файла)
		public static $allowedLangs = array(
			"rus" => ""
		);

		public static function correctPath($path, $file, $default){
			if (file_exists(__DIR__."/../".$path.$file)){
				return $path.$file;
			}
			return $path.$default;
		}

		public static function getCorrectAvatarPath($id){
			return General::correctPath("assets/img/avatars/", "{$id}.png", "default.png");
		}	

		public static function getCorrectTopicIcoPath($path){
			return General::correctPath("assets/img/topics/", $path, "topic-default-ico.svg");
		}	

		public static function getSpritesPath($path){
			$deafultPath = "assets/maps/";
			if (is_dir(__DIR__."/../".$deafultPath.$path.'/')){
				return $deafultPath.$path.'/';
			}
			return $deafultPath;
		}
		public static function getMapPath($path){
			return General::getSpritesPath($path);
		}
		// public static function correctSpritePath($path, $sprite){
		// 	$path = General::getMapSpritesPath($path);
		// 	if (file_exists($path.$sprite)){
		// 		return $path.$file;
		// 	}
		// 	return General::getMapSpritesPath('').$file;
		// }

		public static function getInputMethod(){
			if ($_SERVER["REQUEST_METHOD"] === "POST"){
				return INPUT_POST;
			}
			return INPUT_GET;
		}
    }

	class Brake extends Exception {
    	public $loglvl = LOG_LVL_WARN;

		public function __construct($message, $code = 0, $loglvl = LOG_LVL_WARN, Throwable $previous = null) {
			$this->loglvl = $loglvl;
			parent::__construct($message, $code, $previous);
		}
		public function __toString() {
			return "{$this->message} {$this->file}[{$this->line}]";
			// return ": [{$this->code}]: {$this->message}\n";
		}
	}
?>