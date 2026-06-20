<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "notes_db");

if (!$conn) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// 1. Create table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    department VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'student',
    study_year INT DEFAULT NULL
)";
mysqli_query($conn, $table_sql);

// 2. AUTO-REPAIR: Ensure older databases get the 'role' and 'study_year' columns silently
$check_role = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'role'");
if ($check_role && mysqli_num_rows($check_role) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'student'");
}
$check_year = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'study_year'");
if ($check_year && mysqli_num_rows($check_year) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN study_year INT DEFAULT NULL");
}

// Determine which action the frontend is asking for
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($data['action']) ? $data['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// --- REGISTRATION ---
if ($action == 'register') {
    $name = mysqli_real_escape_string($conn, $data['name']);
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $dept = mysqli_real_escape_string($conn, $data['department']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT); 
    $study_year = intval($data['study_year']);

    $sql = "INSERT INTO users (name, student_id, department, study_year, email, password) VALUES ('$name', '$student_id', '$dept', $study_year, '$email', '$password')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_id'] = mysqli_insert_id($conn);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_dept'] = $dept;
        $_SESSION['user_student_id'] = $student_id;
        $_SESSION['user_study_year'] = $study_year;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Email may already be registered."]);
    }
}

// --- LOGIN ---
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
            $_SESSION['user_study_year'] = $row['study_year'];
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Incorrect password."]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Email not found."]);
    }
}

// --- GET CURRENT USER ---
elseif ($action == 'get_user') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            "logged_in" => true,
            "id" => $_SESSION['user_id'], // NEW: Sends the database ID to the frontend
            "name" => $_SESSION['user_name'],
            "email" => $_SESSION['user_email'],
            "department" => $_SESSION['user_dept'],
            "student_id" => $_SESSION['user_student_id'],
            "study_year" => isset($_SESSION['user_study_year']) ? $_SESSION['user_study_year'] : null
        ]);
    } else {
        echo json_encode(["logged_in" => false]);
    }
}

// --- UPDATE PROFILE ---
elseif ($action == 'update_profile') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "error" => "Not logged in."]);
        exit;
    }

    $id = $_SESSION['user_id'];
    $name = mysqli_real_escape_string($conn, $data['name']);
    $student_id = mysqli_real_escape_string($conn, $data['student_id']);
    $dept = mysqli_real_escape_string($conn, $data['department']);
    $study_year = intval($data['study_year']);
    
    $sql = "UPDATE users SET name='$name', student_id='$student_id', department='$dept', study_year=$study_year WHERE id=$id";

    if (!empty($data['password'])) {
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name='$name', student_id='$student_id', department='$dept', study_year=$study_year, password='$password' WHERE id=$id";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_dept'] = $dept;
        $_SESSION['user_student_id'] = $student_id;
        $_SESSION['user_study_year'] = $study_year;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update profile."]);
    }
}

// --- FETCH ALL USERS (ADMIN ONLY) ---
elseif ($action == 'get_all_users') {
    if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'minhajganikhan2007@gmail.com') {
        echo json_encode(["error" => "Unauthorized access"]);
        exit;
    }

    $sql = "SELECT id, name, student_id, department, email, password, role, study_year FROM users";
    $result = mysqli_query($conn, $sql);
    
    $users = array();
    if ($result) {
        while($row = mysqli_fetch_assoc($result)){
            $users[] = $row;
        }
    }
    echo json_encode($users);
}

// --- DELETE USER (ADMIN ONLY) ---
elseif ($action == 'delete_user') {
    if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'minhajganikhan2007@gmail.com') {
        echo json_encode(["success" => false, "error" => "Unauthorized access"]);
        exit;
    }

    $delete_id = intval($data['id']);

    if ($delete_id == $_SESSION['user_id']) {
        echo json_encode(["success" => false, "error" => "You cannot delete your own active session!"]);
        exit;
    }

    $check_admin_sql = "SELECT email, role FROM users WHERE id = $delete_id";
    $check_result = mysqli_query($conn, $check_admin_sql);
    if ($check_row = mysqli_fetch_assoc($check_result)) {
        if ($check_row['email'] === 'minhajganikhan2007@gmail.com' || $check_row['role'] === 'admin') {
            echo json_encode(["success" => false, "error" => "Security Policy: Administrator accounts cannot be deleted."]);
            exit;
        }
    }

    $protect_notes_sql = "UPDATE notes SET uploaded_by = NULL WHERE uploaded_by = $delete_id";
    mysqli_query($conn, $protect_notes_sql);

    $sql = "DELETE FROM users WHERE id = $delete_id";
    if(mysqli_query($conn, $sql)) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Database error during deletion."]);
    }
}

// --- LOGOUT ---
elseif ($action == 'logout') {
    session_destroy();
    echo json_encode(["success" => true]);
}

mysqli_close($conn);
?>