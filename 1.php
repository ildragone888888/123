<?php
$rand = "1.txt";
$f = fopen ("".$rand."");
$contents = fread($f,filesize("".$rand.""));
fclose($f); 
echo $contents;
