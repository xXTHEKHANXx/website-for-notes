<?php
// 1. Database Connection (Replaces your db.php include)
$conn = mysqli_connect("localhost", "root", "", "notes_db");

if(!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// 2. Ensure a form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ==========================================
    // ACTION 1: REGISTRATION LOGIC
    // (Triggered if the 'name' field is present)
    // ==========================================
    if (isset($_POST['name'])) {
        
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Added these to prevent your database from crashing
        $student_id = $_POST['student_id'];
        $department = $_POST['department'];
        
        // Securely hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (student_id, name, email, password, department) 
                VALUES ('$student_id', '$name', '$email', '$hashed_password', '$department')";

        if(mysqli_query($conn, $sql)) {
            echo "Account Created Successfully!";
        } else {
            echo "Error: Email already exists or missing data.";
        }
    }

    // ==========================================
    // ACTION 2: LOGIN LOGIC
    // (Triggered if 'name' is missing, but email/password exist)
    // ==========================================
    else if (isset($_POST['email']) && isset($_POST['password'])) {
        
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Find the user by their email
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            
            // Grab the user's data row from the database
            $user = mysqli_fetch_assoc($result);

            // Verify the typed password matches the secure hash in the database
            if (password_verify($password, $user['password'])) {
                echo "Login Successful!";
            } else {
                echo "Invalid Email or Password";
            }
        } else {
            echo "Invalid Email or Password";
        }
    }
}

// Close the connection at the very end
mysqli_close($conn);
?>