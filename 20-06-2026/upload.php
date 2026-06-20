<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$status_title = "";
$status_message = "";
$status_color = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $conn = mysqli_connect("localhost", "root", "", "notes_db");

    if(!$conn) {
        $status_title = "Connection Failed";
        $status_message = "Could not connect to the database.";
        $status_color = "var(--danger)";
    } else {
        $department = $_POST['department'];
        $semester = $_POST['semester'];
        $subject = $_POST['subject'];
        $filename = $_FILES['pdf']['name'];
        $tempname = $_FILES['pdf']['tmp_name'];
        $filepath = "upload/".$filename;
        
        // NEW: Grab the currently logged-in user's ID
        $user_id = $_SESSION['user_id']; 

        if(move_uploaded_file($tempname, $filepath)) {
            // NEW: Added 'uploaded_by' and '$user_id' to save the uploader's identity
            $sql = "INSERT INTO notes (title, subject, semester, department, file_path, uploaded_by) 
                    VALUES ('$filename','$subject','$semester', '$department','$filepath', $user_id)";

            if(mysqli_query($conn,$sql)) {
                $status_title = "Notes Uploaded Successfully";
                $status_message = "Your notes have been securely saved and added to the download library.";
                $status_color = "var(--buffer-blue)";
            } else {
                $status_title = "Database Insert Failed";
                $status_message = "The file was uploaded, but the database record failed to save.";
                $status_color = "var(--danger)";
            }
        } else {
            $status_title = "File Upload Failed";
            $status_message = "The server could not move the file to the upload folder.";
            $status_color = "var(--danger)";
        }
    }
} else {
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
                <li id="nav-auth"><a href="auth.html" class="btn btn-outline">Login / Register</a></li>
                <li id="nav-admin" style="display: none;"><a href="admin.html" class="btn" style="background-color: var(--danger); color: white;">Admin Panel</a></li>
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

    <script>
        fetch('website.php?action=get_user').then(res => res.json()).then(user => {
            if(user.logged_in) {
                if(user.email === 'minhajganikhan2007@gmail.com') {
                    document.getElementById('nav-admin').style.display = 'block';
                }

                let displayYear = "Not Set";
                if (user.study_year == 1) displayYear = "1st Year";
                else if (user.study_year == 2) displayYear = "2nd Year";
                else if (user.study_year == 3) displayYear = "3rd Year";
                else if (user.study_year == 4) displayYear = "4th Year";

                const initial = user.name.charAt(0).toUpperCase();
                document.getElementById('nav-auth').innerHTML = `<button id="avatar-btn" style="width: 40px; height: 40px; border-radius: 50%; background-color: var(--primary-accent); color: #000; font-weight: bold; border: none; cursor: pointer; font-size: 1.2rem; display: flex; justify-content: center; align-items: center;">${initial}</button>`;

                const sidebarHTML = `
                    <div id="prof-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); opacity: 0; visibility: hidden; transition: 0.3s; z-index: 999;"></div>
                    <div id="prof-sidebar" style="position: fixed; top: 0; right: -350px; width: 350px; height: 100vh; background-color: var(--bg-surface); box-shadow: -5px 0 15px rgba(0,0,0,0.5); transition: right 0.3s ease; z-index: 1000; display: flex; flex-direction: column;">
                        <div style="padding: 1.5rem; border-bottom: 1px solid var(--bg-surface-light); display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin:0; color: var(--primary-accent);">My Profile</h3>
                            <button id="close-sidebar" style="background: none; border: none; color: var(--text-main); font-size: 1.5rem; cursor: pointer;">&times;</button>
                        </div>
                        <div style="padding: 1.5rem; flex: 1; overflow-y: auto;">
                            <div style="margin-bottom: 1.2rem;"><div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Full Name</div><div style="font-size: 1.1rem; color: var(--text-main); font-weight: bold;">${user.name}</div></div>
                            <div style="margin-bottom: 1.2rem;"><div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Student ID</div><div style="font-size: 1.1rem; color: var(--text-main);">${user.student_id}</div></div>
                            <div style="margin-bottom: 1.2rem;"><div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Department</div><div style="font-size: 1.1rem; color: var(--text-main);">${user.department.toUpperCase()}</div></div>
                            <div style="margin-bottom: 1.2rem;"><div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Year of Study</div><div style="font-size: 1.1rem; color: var(--text-main);">${displayYear}</div></div>
                            <div style="margin-bottom: 1.2rem;"><div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">University Email</div><div style="font-size: 1rem; color: var(--text-main);">${user.email}</div></div>
                        </div>
                        <div style="padding: 1.5rem; border-top: 1px solid var(--bg-surface-light);">
                            <a href="profile.html" class="btn btn-outline" style="display:block; text-align: center; margin-bottom: 1rem;">⚙️ Profile Settings</a>
                            <button onclick="logoutUser()" class="btn" style="width: 100%; background-color: var(--danger); color: white;">Logout</button>
                        </div>
                    </div>`;
                document.body.insertAdjacentHTML('beforeend', sidebarHTML);

                const sidebar = document.getElementById('prof-sidebar');
                const overlay = document.getElementById('prof-overlay');
                const closeSidebar = () => { sidebar.style.right = '-350px'; overlay.style.opacity = '0'; overlay.style.visibility = 'hidden'; };

                document.getElementById('avatar-btn').addEventListener('click', () => { sidebar.style.right = '0'; overlay.style.opacity = '1'; overlay.style.visibility = 'visible'; });
                document.getElementById('close-sidebar').addEventListener('click', closeSidebar);
                overlay.addEventListener('click', closeSidebar);
            }
        });

        function logoutUser() {
            fetch('website.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({action: 'logout'}) })
            .then(() => window.location.href = 'auth.html');
        }
    </script>
</body>
</html>