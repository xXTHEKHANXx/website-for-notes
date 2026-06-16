<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "notes_db");

if (!$conn) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// BULLETPROOF: Automatically create the users table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    department VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'student'
)";
mysqli_query($conn, $table_sql);

// Determine which action the frontend is asking for
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($data['action']) ? $data['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// 1. REGISTRATION
if ($action == 'register') {
    $name = mysqli_real_escape_string($conn, $data['name']);
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $dept = mysqli_real_escape_string($conn, $data['department']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT); // Secure hashing!

    $sql = "INSERT INTO users (name, student_id, department, email, password) VALUES ('$name', '$student_id', '$dept', '$email', '$password')";
    
    if (mysqli_query($conn, $sql)) {
        // Auto-login after registration
        $_SESSION['user_id'] = mysqli_insert_id($conn);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_dept'] = $dept;
        $_SESSION['user_student_id'] = $student_id;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Email may already be registered."]);
    }
}

// 2. LOGIN
elseif ($action == 'login') {
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = $data['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['user_dept'] = $row['department'];
            $_SESSION['user_student_id'] = $row['student_id'];
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Email not found."]);
    }
}

// 3. GET CURRENT USER
elseif ($action == 'get_user') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            "logged_in" => true,
            "name" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email'],
            "department" => $_SESSION['user_dept'],
            "student_id" => $_SESSION['user_student_id']
        ]);
    } else {
        echo json_encode(["logged_in" => false]);
    }
}

// 4. UPDATE PROFILE
elseif ($action == 'update_profile') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "error" => "Not logged in."]);
        exit;
    }

    $id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $data['name']);
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $dept = mysqli_real_escape_string($conn, $data['department']);
    
    $sql = "UPDATE users SET name='$name', student_id='$student_id', department='$dept' WHERE id=$id";

    // Update password only if the user typed a new one
    if (!empty($data['password'])) {
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name='$name', student_id='$student_id', department='$dept', password='$password' WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        // Update the live session variables
        $_SESSION['user_name'] = $name;
        $_SESSION['user_dept'] = $dept;
        $_SESSION['user_student_id'] = $student_id;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update profile."]);
    }
}

// 5. LOGOUT
elseif ($action == 'logout') {
    session_destroy();
    echo json_encode(["success" => true]);
}

mysqli_close($conn);
?>