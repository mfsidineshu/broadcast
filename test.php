<?php


$date = new DateTime();
$date->modify('-5 minutes');
$formatted_date = $date->format('Y-m-d H:i:s');

echo $formatted_date;
?>
