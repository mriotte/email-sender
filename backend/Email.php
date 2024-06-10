<?php

/**
 * Represents an Email entity and provides methods to interact with the database.
 */
class Email
{
    /**
     * Creates a new email record in the database.
     *
     * @param PDO $pdo A PDO instance representing a connection to the database.
     * @param array $data An associative array containing email data (email, name, content).
     * @return bool True on success, false on failure.
     */
    public static function create(PDO $pdo, array $data): bool
    {
        try {
            $stmt = $pdo->prepare('INSERT INTO emails (email, name, content, status) VALUES (?, ?, ?, ?)');
            return $stmt->execute([$data['email'], $data['name'], $data['content'], 'pending']);
        } catch (PDOException $e) {
            error_log('Failed to create email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves pending emails from the database.
     *
     * @param PDO $pdo A PDO instance representing a connection to the database.
     * @param int $limit The number of emails to send in a certain time.
     * @return array An array of pending emails.
     */
    public static function getPendingEmails(PDO $pdo, int $limit): array
    {
        try {
            $stmt = $pdo->prepare('SELECT * FROM emails WHERE status = :status LIMIT :limit');
            $stmt->bindValue(':status', 'pending');
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Failed to get pending emails: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Updates the status of an email record in the database.
     *
     * @param PDO $pdo A PDO instance representing a connection to the database.
     * @param int $id The ID of the email record to update.
     * @param string $status The new status of the email record.
     * @param string|null $error The error message associated with the email record (optional).
     * @return bool True on success, false on failure.
     */
    public static function updateStatus(PDO $pdo, int $id, string $status, ?string $error = null): bool
    {
        try {
            $stmt = $pdo->prepare('UPDATE emails SET status = ?, last_error = ?, sent_at = NOW(), updated_at = NOW() WHERE id = ?');
            return $stmt->execute([$status, $error, $id]);
        } catch (PDOException $e) {
            error_log('Failed to update email status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Counts the number of pending emails in the database.
     *
     * @param PDO $pdo A PDO instance representing a connection to the database.
     * @return int The number of pending emails.
     */
    public static function countPendingEmails(PDO $pdo): int
    {
        try {
            $stmt = $pdo->query('SELECT COUNT(*) FROM emails WHERE status = "pending"');
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Failed to count pending emails: ' . $e->getMessage());
            return 0;
        }
    }
}
