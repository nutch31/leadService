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

$sql = "SELECT * FROM calls where id < '3000'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"]. " - date: " . $row["date"]. " " . $row["created_at"]. "<br>";
    }
} else {
    echo "0 results";
}
$conn->close();
?>