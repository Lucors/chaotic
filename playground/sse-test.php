<?php
	// header("Content-Type: text/event-stream");
	// header("Cache-Control: no-cache");

	// $name = filter_input(INPUT_GET, "name");
	// $time = date("r");

	// echo "data: Hello, {$name}. The server time is: {$time}\n\n";
	// flush();
?>

<?php
	if (!isset($_SESSION)){
		session_start();
	}
	header("Content-Type: text/event-stream\n\n");
	$counter = rand(1, 10);
	while (1) {
		# Отправлять событие "ping" каждую секунду
		
		echo "event: ping\n";
		$curDate = date(DATE_ISO8601);
		echo "data: {'time': '' . $curDate . ''}";
		echo "\n\n";
		
		echo "data: Время сообщения, {$_SESSION['name']}: " . $curDate . "\n\n";
		
		ob_end_flush();
		flush();
		session_reset();
		sleep(1);
	}
?>