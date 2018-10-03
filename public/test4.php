<?php

$request->created_at = "2018-10-03 11:50:30";

echo $StartDateTime = date("Y-m-d H:i:s", (strtotime(date($request->created_at)) - 2));
echo "<BR>";
echo $EndDateTime = date("Y-m-d H:i:s", (strtotime(date($request->created_at)) + 2));

die;

$date = "2018-09-12 12:10:39";
$heronumber = "022158279";
$client_number = "0830479124";
$phone = "0891233120";
$status_text = "Answered";
$duration = "00:10:30";
$recording_url = "http://www.google.com";

$arr = array(
    'timestamp' => $date, 
    'heronumber' => $heronumber, 
    'client_number' => $client_number, 
    'caller_id' => $phone, 
    'status' => $status_text, 
    'duration' => $duration, 
    'recording_url' => $recording_url 
   );

$val = json_encode($arr);

echo $val;