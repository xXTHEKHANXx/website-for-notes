<?php
// We define empty variables here to hold your success/error messages for the HTML
$status_title = "";
$status_message = "";
$status_color = "";

// Your EXACT PHP code structure begins here
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $conn = mysqli_connect(
        "localhost",
        "root",
        "",
        "notes_db"
    );

    if(!$conn)
    {
        $status_title = "Connection Failed";
        $status_message = "Could not connect to the database.";
        $status_color = "var(--danger)";
    }
    else
    {
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
                // Swapped your echo for the UI variables
                $status_title = "Notes Uploaded Successfully";
                $status_message = "Your notes have been securely saved and added to the download library.";
                $status_color = "var(--buffer-blue)";
            }
            else
            {
                // Swapped your echo for the UI variables
                $status_title = "Database Insert Failed";
                $status_message = "The file was uploaded, but the database record failed to save.";
                $status_color = "var(--danger)";
            }
        }
        else
        {
            // Swapped your echo for the UI variables
            $status_title = "File Upload Failed";
            $status_message = "The server could not move the file to the upload folder.";
            $status_color = "var(--danger)";
        }
    }
} else {
    // Failsafe: Send them back to the upload page if they access this URL directly
    header("Location: upload.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UITNotes - Upload Status</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <a href="index.html" class="logo" style="text-decoration: none;">📚 UITNotes</a>
        <nav>
            <ul>
                <li><a href="index.html">Browse Notes</a></li>
                <li><a href="upload.html" class="active">Upload</a></li>
                <li><a href="download.html">Download Notes</a></li>
                <li><a href="auth.html" class="btn btn-outline">Login / Register</a></li>
                <li><a href="admin.html" class="btn" style="background-color: var(--danger); color: white;">Admin Panel</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <section style="text-align: center; padding: 4rem 2rem; max-width: 600px; margin: 0 auto; border-top: 4px solid <?php echo $status_color; ?>;">
            
            <h2 style="color: <?php echo $status_color; ?>; border: none; margin-bottom: 1rem; font-size: 2rem;">
                <?php echo $status_title; ?>
            </h2>
            
            <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 2.5rem;">
                <?php echo $status_message; ?>
            </p>
            
            <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                <a href="upload.html" class="btn btn-outline">Upload Another File</a>
                <a href="download.html" class="btn">Go to Download Library</a>
            </div>

        </section>
    </div>

    <footer>
        <div style="margin-bottom: 1.5rem;">
            <a href="index.html" class="logo" style="text-decoration: none; font-size: 1.5rem;">📚 UITNotes</a>
        </div>
        <div class="footer-links">
            <a href="feedback.html">Submit Feedback</a>
            <a href="bugreport.html">Report a Bug / Error</a>
            <a href="terms.html">Terms of Service</a>
        </div>
        <p style="color: var(--text-muted); font-size: 0.9rem;">&copy; 2026 UITNotes Platform. Built for Students.</p>
    </footer>

</body>
</html>