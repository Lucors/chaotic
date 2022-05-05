<?php
	define("CONF_LOG_DEBUG", 4);
	define("CONF_LOG_MSG", 3);
	define("CONF_LOG_WARN", 2);
	define("CONF_LOG_ERR", 1);

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

		public function getCorrectAvatarPath($id){
			$avatarsPath = "assets/img/avatars/";
			$defaultAvatar = "default.png";

			$userAvatar = "{$id}.png";
			if (file_exists($avatarsPath.$userAvatar)){
				return $avatarsPath.$userAvatar;
			}
			return $avatarsPath.$defaultAvatar;
		}	

		public function getCorrectTopicIcoPath($path){
			$topicsPath = "assets/img/topics/";
			$defaultIcon = "topic-default-ico.svg";
			
			if (file_exists($topicsPath.$path)){
				return $topicsPath.$path;
			}
			return $topicsPath.$defaultIcon;
		}	
    }
?>