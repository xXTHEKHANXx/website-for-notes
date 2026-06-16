<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "notes_db"
);

if (!$conn) {
    die("Connection Failed");
}

$sql = "SELECT * FROM notes ORDER BY upload_date DESC";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['title'] . "</td>";
    echo "<td>" . $row['subject'] . "</td>";
    echo "<td>" . $row['semester'] . "</td>";
    echo "<td>" . $row['department'] . "</td>";
    echo "<td><a href='" . $row['file_path'] . "' download>Download</a></td>";
    echo "</tr>";
}

mysqli_close($conn);

?>