<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

include 'database.php';

if (!class_exists('Email')) {
    include 'Email.php';
}

/**
 * Configures and returns a PHPMailer instance.
 *
 * @return PHPMailer Configured PHPMailer instance.
 * @throws Exception
 */
function configureMailer(): PHPMailer
{
    $mailSettings = include 'settings.php';

    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = $mailSettings['mail_settings_prod']['host'];
    $mail->SMTPAuth = $mailSettings['mail_settings_prod']['auth'];
    $mail->Username = $mailSettings['mail_settings_prod']['username'];
    $mail->Password = $mailSettings['mail_settings_prod']['password'];
    $mail->SMTPSecure = $mailSettings['mail_settings_prod']['secure'];
    $mail->Port = $mailSettings['mail_settings_prod']['port'];

    $mail->setFrom($mailSettings['mail_settings_prod']['from_email'], $mailSettings['mail_settings_prod']['from_name']);

    return $mail;
}

/**
 * Sends pending emails from the database.
 *
 * @param PDO $pdo A PDO instance representing a connection to the database.
 * @param int $limit The number of emails to send in a certain time.
 * @param int $interval The interval between sending emails.
 * @return array The response message and details.
 * @throws Exception
 */
function sendEmails(PDO $pdo, int $limit, int $interval): array
{
    $response = [];

    $pendingCount = Email::countPendingEmails($pdo);

    if ($pendingCount == 0) {
        $response['message'] = 'No pending emails to process';
        return $response;
    }

    $emails = Email::getPendingEmails($pdo, $limit);

    if (empty($emails)) {
        $response['message'] = 'No pending emails to process';
        return $response;
    }

    $response['message'] = 'Sending Emails';
    $response['emails'] = [];

    $mail = configureMailer();

    foreach ($emails as $email) {
        $result = sendEmail($mail, $pdo, $email);
        $response['emails'][] = $result;
    }

    $response['interval'] = $interval;
    return $response;
}

/**
 * Sends an email using PHPMailer and updates its status in the database.
 *
 * @param PHPMailer $mail A PHPMailer instance.
 * @param PDO $pdo A PDO instance representing a connection to the database.
 * @param array $email An associative array containing email data.
 * @return array The result of the email sending process.
 */
function sendEmail(PHPMailer $mail, PDO $pdo, array $email): array
{
    $result = [];

    try {
        $mail->addAddress($email['email'], $email['name']);

        $mail->isHTML(true);
        $mail->Subject = 'Test Subject';
        $mail->Body = $email['content'];

        $mail->send();
        Email::updateStatus($pdo, $email['id'], 'sent');
        $result['status'] = 'success';
        $result['message'] = 'Sent to ' . htmlspecialchars($email['name']) . ' (' . htmlspecialchars($email['email']) . '): ' . htmlspecialchars($email['content']);
    } catch (Exception $e) {
        Email::updateStatus($pdo, $email['id'], 'failed', $mail->ErrorInfo);
        $result['status'] = 'error';
        $result['message'] = 'Failed to send to ' . htmlspecialchars($email['name']) . ' (' . htmlspecialchars($email['email']) . '): ' . htmlspecialchars($mail->ErrorInfo);
    }

    return $result;
}

if (php_sapi_name() !== 'cli') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
        $interval = isset($_POST['interval']) ? (int)$_POST['interval'] : 60;
        if (isset($pdo)) {
            echo json_encode(sendEmails($pdo, $limit, $interval));
        } else {
            echo json_encode(["error" => "Database connection is not established."]);
        }
    } else {
        echo json_encode(["error" => "Invalid request method"]);
    }
}
