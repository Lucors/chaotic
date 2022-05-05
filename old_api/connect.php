<?php
    require_once("log.php");

	// Класс соединения с БД (Обертка mysqli)
	class Connection {
		private $username;
		private $password;
		public $persistent;
		public $hostname;
		public $dbname;
		public $mysqli = null;
		public $result = null;

		function __construct($host = null, $user = null, $pass = null, $dbname = null, $p = true){
			$this->config($host, $user, $pass, $dbname, $p);
		}
		function __destruct(){
			$this->disconnect(!$this->persistent);
		}

		private function mysqlError($msg){
			Log::error("Ошибка MYSQL: ".$msg);
			$this->result = null;
		}

		static public function isMysqli($some){
			if (!is_null($some)){
				if ($some instanceof mysqli){
					return true;
				}
			}
			return false;
		}
		//Проверяет, что результат является mysqli_result
		static public function isMysqliResult($some){
			if (!is_null($some)){
				return ($some instanceof mysqli_result) ? true : false;
			}
			return false;
		}
		//Проверяет, что результат является корректным 
		//и непустым объектом mysqli_result
		static public function isMysqliResultValid($some){
			if (Connection::isMysqliResult($some)){
				if ($some->num_rows != 0){
					return true;
				}
			}
			return false;
		}
		//Проверяет, что результат является положительным
		static public function isQueryResultValid($some){
			if (!is_null($some)){
				if (Connection::isMysqliResultValid($some)){
					return true;
				}
				return ($some === true) ? true : false;
			}
			return false;
		} 


		public function free(){
			if ($this->isMysqliResult($this->result)){
				@$this->result->free();
			}
		}
		public function config($host, $user, $pass, $dbname, $p = false){
			$this->hostname = $host;
			$this->username = $user;
			$this->password = $pass;
			$this->dbname = $dbname;
			$this->persistent = $p;
		}
		public function connect(){
			if ($this->isMysqli($this->mysqli)){
				return Log::warning("Соединение уже существует");
			}
			else {
				Log::debug("Попытка подключения к БД: ".$this->dbname);
				if ($this->persistent){
					$this->mysqli = @new mysqli("p:".$this->hostname, $this->username, $this->password, $this->dbname);
				}
				else {
					$this->mysqli = @new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
				}
				if ($this->mysqli->connect_error){
					$this->mysqlError("({$this->mysqli->connect_errno}) {$this->mysqli->connect_error}");
					$this->mysqli = null;
					return;
				}
				Log::debug("Успешное подключение к БД: ".$this->dbname);
			}
		}
		public function reconnect(){
			Log::debug("Попытка переподключения к БД: ".$this->dbname);
			$this->disconnect();
			$this->connect($this->hostname, $this->username, $this->password, $this->dbname, $this->persistent);
		}
		public function disconnect($forced = true){
			$this->free();
			if (!$this->persistent || $forced){
				if ($this->isMysqli($this->mysqli)){
					@$this->mysqli->close();
					$this->mysqli = null;
					Log::debug("Закрыто соединение с БД: ".$this->dbname);
				}
			}
		}
		public function query($query, $callback = null){
			$this->free();
			$this->result = $this->mysqli->query($query);
			Log::debug("Произведен запрос к БД: ".$this->dbname);

			if ($this->result === false){
				$this->mysqlError($this->mysqli->error);
				return null;
			}
			if (is_callable($callback)){
				return $callback($this->result);
			}
			return $this->result;
		}
	}
?>