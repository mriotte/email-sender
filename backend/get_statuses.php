<?php

include 'database.php';

try {
    if (!empty($pdo)) {
        $stmt = $pdo->query('SELECT * FROM emails');
        $emails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($emails);
    } else {
        echo json_encode(["error" => "Database connection is not established."]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
