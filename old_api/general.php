<?php
	define("CONF_LOG_DEBUG", 4);
	define("CONF_LOG_MSG", 3);
	define("CONF_LOG_WARN", 2);
	define("CONF_LOG_ERR", 1);

	define("USTAGE_INAUTH", 0);
	define("USTAGE_INMENU", 1);
	define("USTAGE_INROOM_LOADING", 2);
	define("USTAGE_INROOM_NOTREADY", 3);
	define("USTAGE_INROOM_READY", 4);
	define("USTAGE_INGAME", 5);


    class General {
    	public static $allowLogging = true;
		public static $loggingPath = "assets/log/"; 
		public static $loggingLevel = CONF_LOG_MSG;

		public static $dbHost = "localhost";
		public static $dbUser = "u3905860fl_admin";
		public static $dbPass = "Nb8p1XvtKG";
		public static $dbName = "u3905860fl_admin";

		public static $allowedLangs = array(
			"rus" => ""
		);

		public static function correctPath($path, $file, $default){
			if (file_exists($path.$file)){
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
			$deafultPath = "assets/img/maps/";
			if (is_dir($deafultPath.$path.'/')){
				return $deafultPath.$path.'/';
			}
			return $deafultPath;
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
?>