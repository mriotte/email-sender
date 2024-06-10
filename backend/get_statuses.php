<?php

include 'database.php';

try {
    if (!empty($pdo)) {
        // Querying the database to select all records from the 'emails' table
        $stmt = $pdo->query('SELECT * FROM emails');
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Returning the emails as a JSON response
        echo json_encode($emails);
    } else {
        echo json_encode(["error" => "Database connection is not established."]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
