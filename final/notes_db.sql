CREATE DATABASE notes_db;

USE notes_db;

-- Users table
CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255) NOT NULL,
    department VARCHAR(50)
);

-- Notes table
CREATE TABLE notes(
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    subject VARCHAR(100),
    semester INT,
    department VARCHAR(50),
    file_path VARCHAR(255),
    uploaded_by INT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(uploaded_by)
    REFERENCES users(id)
);

-- Feedback table
CREATE TABLE feedback(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);