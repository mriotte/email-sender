<?php

include 'database.php';

if (!class_exists('Email')) {
    include 'Email.php';
}

if (php_sapi_name() !== 'cli') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file_path = $_FILES['file']['tmp_name'];
            $rows = parseCSVFile($file_path);

            if (count($rows) > 0) {
                if (isset($pdo)) {
                    $count = processParsedData($rows, $pdo);
                }
                echo json_encode(["message" => "$count records processed successfully"]);
            } else {
                echo json_encode(["error" => "Invalid CSV format or no data found"]);
            }
        } else {
            echo json_encode(["error" => "File upload error. Please make sure you selected a file"]);
        }
    } else {
        echo json_encode(["error" => "Invalid request method"]);
    }
}

/**
 * Parses a CSV file and returns an array of data.
 *
 * @param string $file_path The path to the CSV file.
 * @return array An array of associative arrays containing email data.
 */
function parseCSVFile(string $file_path): array
{
    $file = fopen($file_path, 'r');
    $header = fgetcsv($file);

    if ($header === false || count($header) < 3) {
        fclose($file);
        return [];
    }

    $rows = [];
    while (($row = fgetcsv($file)) !== false) {
        if (count($row) >= 3) {
            $rows[] = [
                'email' => $row[0],
                'name' => $row[1],
                'content' => $row[2],
            ];
        }
    }
    fclose($file);
    return $rows;
}

/**
 * Processes the parsed CSV data and inserts it into the database.
 *
 * @param array $rows An array of associative arrays containing email data.
 * @param PDO $pdo A PDO instance representing a connection to the database.
 * @return int The number of records successfully inserted.
 */
function processParsedData(array $rows, PDO $pdo): int
{
    $count = 0;
    foreach ($rows as $data) {
        if (Email::create($pdo, $data)) {
            $count++;
        }
    }
    return $count;
}

