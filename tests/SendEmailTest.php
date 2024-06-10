<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

require_once 'backend/send_emails.php';

/**
 * Class SendEmailTest
 *
 * Tests the sendEmail function.
 */
class SendEmailTest extends TestCase
{
    /**
     * @var PDO Mocked PDO instance
     */
    private $pdo;

    /**
     * @var PHPMailer Mocked PHPMailer instance
     */
    private $mailer;

    /**
     * @var array Mock email data
     */
    private $email;

    /**
     * Sets up test environment by creating mock objects.
     */
    protected function setUp(): void
    {
        // Create a mock PDO instance
        $this->pdo = $this->createMock(PDO::class);

        // Create a mock PDOStatement instance
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('rowCount')->willReturn(1);

        // Configure the PDO mock to return the mock statement
        $this->pdo->method('prepare')->willReturn($stmt);

        // Create a mock PHPMailer instance
        $this->mailer = $this->createMock(PHPMailer::class);

        // Mock email data
        $this->email = [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'content' => 'This is a test email.'
        ];
    }

    /**
     * Tests successful email sending.
     */
    public function testSendEmailSuccess(): void
    {
        // Configure the mailer mock to simulate a successful send
        $this->mailer->method('send')->willReturn(true);

        $result = sendEmail($this->mailer, $this->pdo, $this->email);

        // Assert that the email was sent successfully
        $this->assertEquals('success', $result['status']);
        $this->assertStringContainsString('Sent to Test User (test@example.com)', $result['message']);
    }

    /**
     * Tests email sending failure.
     */
    public function testSendEmailFailure(): void
    {
        // Configure the mailer mock to simulate a failure in sending
        $this->mailer->method('send')->will($this->throwException(new Exception('SMTP Error')));

        $result = sendEmail($this->mailer, $this->pdo, $this->email);

        // Assert that the email failed to send
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('Failed to send to Test User (test@example.com)', $result['message']);
    }
}
