<?php
// Tell the browser we are sending a JSON response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = mysqli_connect("localhost", "root", "", "notes_db");

    if (!$conn) {
        echo json_encode(["success" => false, "error" => "Database connection failed"]);
        exit;
    }

    // Grab the Note ID sent from the admin's JavaScript
    $data = json_decode(file_get_contents("php://input"), true);
    $note_id = $data['id'];

    if (isset($note_id)) {
        // 1. Find the file path so we can delete the actual PDF from the folder
        $sql = "SELECT file_path FROM notes WHERE id = $note_id";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $filepath = $row['file_path'];

            // 2. Delete the physical file from the 'upload/' folder
            if (file_exists($filepath)) {
                unlink($filepath); // This is the PHP command to delete a file!
            }

            // 3. Delete the record from the database
            $delete_sql = "DELETE FROM notes WHERE id = $note_id";
            
            if (mysqli_query($conn, $delete_sql)) {
                echo json_encode(["success" => true]);
            } else {
                echo json_encode(["success" => false, "error" => "Could not delete from database."]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Note not found in database."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "No Note ID provided."]);
    }
    mysqli_close($conn);
}
?>