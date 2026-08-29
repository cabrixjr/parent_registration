CREATE DATABASE IF NOT EXISTS kibaha_sec_db;
USE kibaha_sec_db;

-- 1. Table for class student roster
CREATE TABLE IF NOT EXISTS students_list (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    admission_no VARCHAR(30) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Table for parent attendance records
CREATE TABLE IF NOT EXISTS parents_attendence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    parent_name VARCHAR(120) NOT NULL,
    phone_number VARCHAR(25) NOT NULL,
    attended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students_list(id) ON DELETE CASCADE
);

-- 3. Table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed initial admin user (Username: admin | Password: adminpassword)
INSERT INTO users (username, password_hash, full_name) VALUES 
('admin', '$2y$10$vD.8S.GkY.1Xb5zZ5zZ5zOqB4v9O0eA7o6A5B4C3D2E1F0G1H2I3J', 'School Administrator');

-- Seed initial class roster (Sample students)
INSERT INTO students_list (full_name, admission_no) VALUES 
('Baraka Juma', 'KSS/2026/001'),
('Aisha Hassan', 'KSS/2026/002'),
('Emmanuel Joseph', 'KSS/2026/003'),
('Grace Peter', 'KSS/2026/004'),
('Kelvin John', 'KSS/2026/005');