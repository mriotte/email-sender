<?php
$settings = include('settings.php');

// Extracting database connection parameters from the settings
$dsn = $settings['database']['dsn'];
$username = $settings['database']['username'];
$password = $settings['database']['password'];

try {
    // Creating a PDO instance to interact with the database
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Connection failed: ' . $e->getMessage());
    echo 'Connection failed. Please check the logs for more details.';
    exit;
}

// SQL commands for creating the database and table
/*
CREATE DATABASE email_sender;

USE email_sender;

CREATE TABLE emails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    sent_at DATETIME NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_error TEXT NULL
);
*/
