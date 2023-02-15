<?php
$d = $_GET["get"];
$dd = $_POST["post"];
$ddd = ".$d."".$dd.";
$fd = fopen("1.txt", 'w');
fwrite($fd, $ddd);
fclose($fd);
