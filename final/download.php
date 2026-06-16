<?php
// 1. Tell the browser we are sending raw JSON data
header('Content-Type: application/json');

// 2. Connect to the database
$conn = mysqli_connect("localhost", "root", "", "notes_db");

if (!$conn) {
    echo json_encode(["error" => "Connection Failed"]);
    exit();
}

// 3. Fetch the notes from newest to oldest
$sql = "SELECT * FROM notes ORDER BY upload_date DESC";
$result = mysqli_query($conn, $sql);

$notesArray = array();

// 4. Package the database rows into our array
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notesArray[] = $row;
    }
}

// 5. Convert the array into JSON and send it to the HTML file
echo json_encode($notesArray);

mysqli_close($conn);
?>