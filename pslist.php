<?php
    $result = shell_exec("ps -A");
    $count = count(preg_split("/\n/", $result)) -2;
    $result = nl2br($result);
    echo "result: {$result}";
    echo "count: {$count}";
?>