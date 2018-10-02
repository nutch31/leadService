<?php
$servername = "127.0.0.1";
$username = "admin";
$password = "GogoHero";
$dbname = "leadservice";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "SELECT channel_id FROM channels where channel_id in ('690', '1241', '1025', '689', '346', '265', '258', '51', '508', '331', '219', '396', '18', '214', '2343', '407', '80', '138', '511', '626', '90', '1198', '1324', '75', '629', '1855', '1856', '1857', '483', '3641', '343', '163', '262', '264', '790', '82', '527', '528', '178', '185', '92', '1521', '1523', '1520', '1522', '2830', '1872', '1870', '1871', '1869', '1560', '1559', '604', '605', '34', '2557', '243', '431', '582', '719', '676', '1236', '859', '1397', '694', '756', '530', '13', '27', '711', '1319', '1917', '847', '49', '1042', '1040', '919', '1038', '68', '323', '1401', '561', '484', '171', '172', '358', '452', '73', '388', '2006', '3611', '198', '195', '142', '375', '376', '227')";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

        if($row["channel_id"] == '690')
        {
            $tracking_phone = "21068453";
        }
        else if($row["channel_id"] == '1241')
        {
            $tracking_phone = "21539524";
        }
        else if($row["channel_id"] == '1025')
        {
            $tracking_phone = "21068434";
        }
        else if($row["channel_id"] == '689')
        {
            $tracking_phone = "21068474";
        }
        else if($row["channel_id"] == '346')
        {
            $tracking_phone = "21068421";
        }
        else if($row["channel_id"] == '265')
        {
            $tracking_phone = "21068430";
        }
        else if($row["channel_id"] == '258')
        {
            $tracking_phone = "21068491";
        }
        else if($row["channel_id"] == '51')
        {
            $tracking_phone = "21068442";
        }
        else if($row["channel_id"] == '508')
        {
            $tracking_phone = "21068410";
        }
        else if($row["channel_id"] == '331')
        {
            $tracking_phone = "21068441";
        }
        else if($row["channel_id"] == '219')
        {
            $tracking_phone = "21068434";
        }
        else if($row["channel_id"] == '396')
        {
            $tracking_phone = "21068487";
        }
        else if($row["channel_id"] == '18')
        {
            $tracking_phone = "21068492";
        }
        else if($row["channel_id"] == '214')
        {
            $tracking_phone = "21068465";
        }
        else if($row["channel_id"] == '2343')
        {
            $tracking_phone = "20262144";
        }
        else if($row["channel_id"] == '407')
        {
            $tracking_phone = "21068427";
        }
        else if($row["channel_id"] == '80')
        {
            $tracking_phone = "21068477";
        }
        else if($row["channel_id"] == '138')
        {
            $tracking_phone = "21068471";
        }
        else if($row["channel_id"] == '511')
        {
            $tracking_phone = "21068412";
        }
        else if($row["channel_id"] == '626')
        {
            $tracking_phone = "21068489";
        }
        else if($row["channel_id"] == '90')
        {
            $tracking_phone = "21068448";
        }
        else if($row["channel_id"] == '1198')
        {
            $tracking_phone = "21539520";
        }
        else if($row["channel_id"] == '1324')
        {
            $tracking_phone = "20262101";
        }
        else if($row["channel_id"] == '75')
        {
            $tracking_phone = "21068444";
        }
        else if($row["channel_id"] == '629')
        {
            $tracking_phone = "21068496";
        }
        else if($row["channel_id"] == '1855')
        {
            $tracking_phone = "20262125";
        }
        else if($row["channel_id"] == '1856')
        {
            $tracking_phone = "20262126";
        }
        else if($row["channel_id"] == '1857')
        {
            $tracking_phone = "20262128";
        }
        else if($row["channel_id"] == '483')
        {
            $tracking_phone = "21068455";
        }
        else if($row["channel_id"] == '3641')
        {
            $tracking_phone = "20262368";
        }
        else if($row["channel_id"] == '343')
        {
            $tracking_phone = "21068435";
        }
        else if($row["channel_id"] == '163')
        {
            $tracking_phone = "21068481";
        }
        else if($row["channel_id"] == '262')
        {
            $tracking_phone = "21068431";
        }
        else if($row["channel_id"] == '264')
        {
            $tracking_phone = "21068432";
        }
        else if($row["channel_id"] == '790')
        {
            $tracking_phone = "21068442";
        }
        else if($row["channel_id"] == '82')
        {
            $tracking_phone = "21068484 ";
        }
        else if($row["channel_id"] == '527')
        {
            $tracking_phone = "21068413";
        }
        else if($row["channel_id"] == '528')
        {
            $tracking_phone = "21068414";
        }
        else if($row["channel_id"] == '178')
        {
            $tracking_phone = "21068446";
        }
        else if($row["channel_id"] == '185')
        {
            $tracking_phone = "21068447";
        }
        else if($row["channel_id"] == '92')
        {
            $tracking_phone = "21068486";
        }
        else if($row["channel_id"] == '1521')
        {
            $tracking_phone = "21068455";
        }
        else if($row["channel_id"] == '1523')
        {
            $tracking_phone = "21068491";
        }
        else if($row["channel_id"] == '1520')
        {
            $tracking_phone = "21068407";
        }
        else if($row["channel_id"] == '1522')
        {
            $tracking_phone = "21068477";
        }
        else if($row["channel_id"] == '2830')
        {
            $tracking_phone = "21068447";
        }
        else if($row["channel_id"] == '1872')
        {
            $tracking_phone = "21068407";
        }
        else if($row["channel_id"] == '1870')
        {
            $tracking_phone = "21068477";
        }
        else if($row["channel_id"] == '1871')
        {
            $tracking_phone = "21068455";
        }
        else if($row["channel_id"] == '1869')
        {
            $tracking_phone = "21068491";
        }
        else if($row["channel_id"] == '1560')
        {
            $tracking_phone = "20262118";
        }
        else if($row["channel_id"] == '1559')
        {
            $tracking_phone = "20262115";
        }
        else if($row["channel_id"] == '604')
        {
            $tracking_phone = "21068497";
        }
        else if($row["channel_id"] == '605')
        {
            $tracking_phone = "21068498";
        }
        else if($row["channel_id"] == '34')
        {
            $tracking_phone = "21068491";
        }
        else if($row["channel_id"] == '2557')
        {
            $tracking_phone = "20262152";
        }
        else if($row["channel_id"] == '243')
        {
            $tracking_phone = "21068476";
        }
        else if($row["channel_id"] == '431')
        {
            $tracking_phone = "21068426";
        }
        else if($row["channel_id"] == '582')
        {
            $tracking_phone = "21068482";
        }
        else if($row["channel_id"] == '719')
        {
            $tracking_phone = "21068447";
        }
        else if($row["channel_id"] == '676')
        {
            $tracking_phone = "21068494";
        }
        else if($row["channel_id"] == '1236')
        {
            $tracking_phone = "21068411";
        }
        else if($row["channel_id"] == '859')
        {
            $tracking_phone = "21068450";
        }
        else if($row["channel_id"] == '1397')
        {
            $tracking_phone = "20262104";
        }
        else if($row["channel_id"] == '694')
        {
            $tracking_phone = "21068481";
        }
        else if($row["channel_id"] == '756')
        {
            $tracking_phone = "21068451";
        }
        else if($row["channel_id"] == '530')
        {
            $tracking_phone = "21068415";
        }
        else if($row["channel_id"] == '13')
        {
            $tracking_phone = "21068475";
        }
        else if($row["channel_id"] == '27')
        {
            $tracking_phone = "21068493";
        }
        else if($row["channel_id"] == '711')
        {
            $tracking_phone = "21068446";
        }
        else if($row["channel_id"] == '1319')
        {
            $tracking_phone = "21068460";
        }
        else if($row["channel_id"] == '1917')
        {
            $tracking_phone = "21068460";
        }
        else if($row["channel_id"] == '847')
        {
            $tracking_phone = "21055825";
        }
        else if($row["channel_id"] == '49')
        {
            $tracking_phone = "21068469";
        }
        else if($row["channel_id"] == '1042')
        {
            $tracking_phone = "21068414";
        }
        else if($row["channel_id"] == '1040')
        {
            $tracking_phone = "21068413";
        }
        else if($row["channel_id"] == '919')
        {
            $tracking_phone = "21068499";
        }
        else if($row["channel_id"] == '1038')
        {
            $tracking_phone = "21068435";
        }
        else if($row["channel_id"] == '68')
        {
            $tracking_phone = "21068463";
        }
        else if($row["channel_id"] == '323')
        {
            $tracking_phone = "21068422";
        }
        else if($row["channel_id"] == '1401')
        {
            $tracking_phone = "20262107";
        }
        else if($row["channel_id"] == '561')
        {
            $tracking_phone = "21068418";
        }
        else if($row["channel_id"] == '484')
        {
            $tracking_phone = "21068450";
        }
        else if($row["channel_id"] == '171')
        {
            $tracking_phone = "21068462";
        }
        else if($row["channel_id"] == '172')
        {
            $tracking_phone = "21068461";
        }
        else if($row["channel_id"] == '358')
        {
            $tracking_phone = "21068424";
        }
        else if($row["channel_id"] == '452')
        {
            $tracking_phone = "21068411";
        }
        else if($row["channel_id"] == '73')
        {
            $tracking_phone = "21068449";
        }
        else if($row["channel_id"] == '388')
        {
            $tracking_phone = "21068438";
        }
        else if($row["channel_id"] == '2006')
        {
            $tracking_phone = "20262107";
        }
        else if($row["channel_id"] == '3611')
        {
            $tracking_phone = "20262366";
        }
        else if($row["channel_id"] == '198')
        {
            $tracking_phone = "21068453";
        }
        else if($row["channel_id"] == '195')
        {
            $tracking_phone = "21068452";
        }
        else if($row["channel_id"] == '142')
        {
            $tracking_phone = "21068472";
        }
        else if($row["channel_id"] == '375')
        {
            $tracking_phone = "21068450";
        }
        else if($row["channel_id"] == '376')
        {
            $tracking_phone = "21068451";
        }
        else if($row["channel_id"] == '227')
        {
            $tracking_phone = "21068443";
        }

        $update = "update channels set tracking_phone = '".$tracking_phone."' where channel_id = '".$row["channel_id"]."' ";
        $conn->query($update);
    }
} else {
    echo "0 results";
}
$conn->close();
?>