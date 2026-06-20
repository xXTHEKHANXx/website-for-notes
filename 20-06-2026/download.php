<?php
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "notes_db");

if (!$conn) {
    echo json_encode(["error" => "Connection Failed"]);
    exit();
}

// NEW: Use LEFT JOIN to combine the notes table with the users table to get the name
$sql = "SELECT notes.*, users.name AS uploader_name 
        FROM notes 
        LEFT JOIN users ON notes.uploaded_by = users.id 
        ORDER BY notes.upload_date DESC";
$result = mysqli_query($conn, $sql);

$notesArray = array();

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notesArray[] = $row;
    }
}

echo json_encode($notesArray);
mysqli_close($conn);
?>