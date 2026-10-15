<?php

namespace NovaFlow\Core;

/**
 * High-Performance CSV Streaming & Export Service
 * Zero external dependencies, memory-efficient streaming for ERP reports & data exports
 */
class CsvExport
{
    /**
     * Stream and force download CSV directly to the browser with UTF-8 BOM for Excel support
     *
     * @param string $filename Name of downloaded file (e.g. "sales-report.csv")
     * @param array $headers Column header row (e.g. ['Invoice #', 'Customer', 'Amount', 'Date'])
     * @param iterable $rows Array or Generator of data rows
     */
    public static function download(string $filename, array $headers, iterable $rows): void
    {
        if (ob_get_length()) {
            ob_end_clean();
        }

        if (!str_ends_with(strtolower($filename), '.csv')) {
            $filename .= '.csv';
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM so Microsoft Excel displays Bengali and special characters correctly
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (!empty($headers)) {
            fputcsv($output, $headers);
        }

        foreach ($rows as $row) {
            if (is_object($row)) {
                $row = (array)$row;
            }
            fputcsv($output, (array)$row);
        }

        fclose($output);
        exit;
    }

    /**
     * Convert data rows to a CSV string (ideal for background jobs, email attachments, or logging)
     */
    public static function toString(array $headers, iterable $rows): string
    {
        $fp = fopen('php://temp', 'r+');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        if (!empty($headers)) {
            fputcsv($fp, $headers);
        }

        foreach ($rows as $row) {
            if (is_object($row)) {
                $row = (array)$row;
            }
            fputcsv($fp, (array)$row);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return $csv;
    }
}
