<?php

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use NovaFlow\Core\CsvExport;

class CsvExportTest extends TestCase
{
    public function testCsvToStringConversion(): void
    {
        $headers = ['ID', 'Customer', 'Amount', 'Currency'];
        $rows = [
            ['101', 'Acme Corp', '1500.00', 'USD'],
            ['102', 'Globex', '2300.50', 'EUR'],
            ['103', 'রফিক স্টোর', '5000.00', 'BDT'], // Bengali text test
        ];

        $csv = CsvExport::toString($headers, $rows);

        // Verify UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);

        // Verify headers
        $this->assertStringContainsString('ID,Customer,Amount,Currency', $csv);

        // Verify rows
        $this->assertStringContainsString('Acme Corp', $csv);
        $this->assertStringContainsString('রফিক স্টোর', $csv);
    }

    public function testCsvWithObjectRows(): void
    {
        $headers = ['Name', 'Role'];
        $rows = [
            (object)['name' => 'Alice', 'role' => 'Admin'],
            (object)['name' => 'Bob', 'role' => 'Manager'],
        ];

        $csv = CsvExport::toString($headers, $rows);
        $this->assertStringContainsString('Alice,Admin', $csv);
        $this->assertStringContainsString('Bob,Manager', $csv);
    }
}
