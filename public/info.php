<?php
$to_time = strtotime('2019-03-18 16:36:39');
$from_time = strtotime('2019-03-18 16:36:02');
$duration=round(abs($to_time - $from_time),2);
echo $duration;
?>