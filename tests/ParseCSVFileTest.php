<?php

use PHPUnit\Framework\TestCase;

require_once 'backend/upload.php';

/**
 * Class ParseCSVFileTest
 *
 * Tests the parseCSVFile function.
 */
class ParseCSVFileTest extends TestCase
{
    /**
     * @var string Path to a valid CSV file
     */
    private string $validCsvFile;

    /**
     * @var string Path to an invalid CSV file
     */
    private string $invalidCsvFile;

    /**
     * @var string Path to an empty CSV file
     */
    private string $emptyCsvFile;

    /**
     * Sets up test environment by creating temporary CSV files.
     */
    protected function setUp(): void
    {
        // Create a valid CSV file
        $this->validCsvFile = tempnam(sys_get_temp_dir(), 'valid');
        file_put_contents($this->validCsvFile, "email,name,content\nexample@example.com,Example Name,Hello World\n");

        // Create an invalid CSV file (missing content column)
        $this->invalidCsvFile = tempnam(sys_get_temp_dir(), 'invalid');
        file_put_contents($this->invalidCsvFile, "email,name\nexample@example.com,Example Name\n");

        // Create an empty CSV file
        $this->emptyCsvFile = tempnam(sys_get_temp_dir(), 'empty');
        file_put_contents($this->emptyCsvFile, "");
    }

    /**
     * Cleans up test environment by deleting temporary CSV files.
     */
    protected function tearDown(): void
    {
        // Delete the temporary files created for the tests
        if (file_exists($this->validCsvFile)) {
            unlink($this->validCsvFile);
        }
        if (file_exists($this->invalidCsvFile)) {
            unlink($this->invalidCsvFile);
        }
        if (file_exists($this->emptyCsvFile)) {
            unlink($this->emptyCsvFile);
        }
    }

    /**
     * Tests parsing of a valid CSV file.
     */
    public function testParseValidCSVFile(): void
    {
        $rows = parseCSVFile($this->validCsvFile);
        $this->assertCount(1, $rows);
        $this->assertEquals('example@example.com', $rows[0]['email']);
        $this->assertEquals('Example Name', $rows[0]['name']);
        $this->assertEquals('Hello World', $rows[0]['content']);
    }

    /**
     * Tests parsing of an invalid CSV file.
     */
    public function testParseInvalidCSVFile(): void
    {
        $rows = parseCSVFile($this->invalidCsvFile);
        $this->assertCount(0, $rows);
    }

    /**
     * Tests parsing of an empty CSV file.
     */
    public function testParseEmptyCSVFile(): void
    {
        $rows = parseCSVFile($this->emptyCsvFile);
        $this->assertCount(0, $rows);
    }
}
