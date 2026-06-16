<?php
if($_SERVER["REQUEST_METHOD"]=="POST"){
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "notes_db"
);

if(!$conn)
{
    die("Connection Failed");
}

$department = $_POST['department'];
$semester = $_POST['semester'];
$subject = $_POST['subject'];

$filename = $_FILES['pdf']['name'];
$tempname = $_FILES['pdf']['tmp_name'];

$filepath = "upload/".$filename;

if(move_uploaded_file($tempname, $filepath))
{
    $sql = "INSERT INTO notes
    (title, subject, semester, department, file_path)
    VALUES
    ('$filename','$subject','$semester',
    '$department','$filepath')";

    if(mysqli_query($conn,$sql))
    {
        echo "Notes Uploaded Successfully";
    }
    else
    {
        echo "Database Insert Failed";
    }
}
else
{
    echo "File Upload Failed";
}
}
?>