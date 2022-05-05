<?php
    require_once("general.php");

    class Log {
	    public static function err($msg){
	    	Log::error($msg);
	    }
	    public static function error($msg){
	    	if (General::$allowLogging){
	    		Log::write("ERROR", $msg);
	    	}
	    }

	    public static function warn($msg){
	    	Log::warning($msg);
	    }
	    public static function warning($msg){
	    	if (General::$allowLogging && General::$loggingLevel >= CONF_LOG_WARN){
	    		Log::write("WARN", $msg);
	    	}
	    }

	    public static function msg($msg){
	    	Log::message($msg);
	    }
	    public static function message($msg){
	    	if (General::$allowLogging && General::$loggingLevel >= CONF_LOG_MSG){
	    		Log::write("MSG", $msg);
	    	}
	    }

	    public static function debug($msg){
	    	if (General::$allowLogging && General::$loggingLevel == CONF_LOG_DEBUG){
	    		Log::write("DEBUG", $msg);
	    	}
	    }

	    public static function write($strtype, $msg){
	    	$output = "<".date("H:i:s")."> [{$strtype}] {$msg}\n";
	    	$fhandle = fopen(General::$loggingPath.date("d-m-Y").".log", "a");
	    	if ($fhandle === false){
	    		die("Невозможно открыть лог. файл \"".General::$loggingPath.date("d-m-Y").".log"."\". Свяжитесь с администрацией.<br>");
	    	}
	    	flock($fhandle, LOCK_EX);
	    	fwrite($fhandle, $output);
	    	fclose($fhandle);
	    }
    }
?>