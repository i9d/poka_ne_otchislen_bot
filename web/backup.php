<?php
$html = file_get_contents('http://fkn.univer.omsk.su/academics/Schedule/schedule3_1.htm');
$backup = "backup.html"
file_put_contents($backup, $html);
?>
