<?php

/**
 * PDF Text Extractor
 * Utility class to extract text content from PDF files for full-text search
 */
class PdfTextExtractor {

    /**
     * Extract text from PDF file using multiple methods
     * Tries pdftotext command first, falls back to PHP parser library
     *
     * @param string $pdfPath Absolute path to PDF file
     * @return string|false Extracted text or false on failure
     */
    public static function extractText($pdfPath) {
        if (!file_exists($pdfPath)) {
            error_log("PDF file not found: $pdfPath");
            return false;
        }

        // Method 1: Try pdftotext command (fastest and most reliable)
        $text = self::extractWithPdfToText($pdfPath);
        if ($text !== false) {
            return $text;
        }

        // Method 2: Try Smalot PDF Parser library (pure PHP)
        $text = self::extractWithSmalotParser($pdfPath);
        if ($text !== false) {
            return $text;
        }

        // Method 3: Basic PDF text extraction (fallback)
        $text = self::extractBasic($pdfPath);

        return $text;
    }

    /**
     * Extract text from PDF with page-level tracking
     * Returns an array with page numbers as keys and text content as values
     *
     * @param string $pdfPath Absolute path to PDF file
     * @return array|false Array of ['pages' => [1 => 'text', 2 => 'text', ...], 'full_text' => 'combined'] or false on failure
     */
    public static function extractTextByPage($pdfPath) {
        if (!file_exists($pdfPath)) {
            error_log("PDF file not found: $pdfPath");
            return false;
        }

        // Try Smalot PDF Parser library (supports page-by-page extraction)
        $result = self::extractByPageWithSmalot($pdfPath);
        if ($result !== false) {
            return $result;
        }

        // Fallback: Extract all text and create a single-page result
        $fullText = self::extractText($pdfPath);
        if ($fullText !== false) {
            return [
                'pages' => [1 => $fullText],
                'full_text' => $fullText,
                'page_count' => 1
            ];
        }

        return false;
    }

    /**
     * Extract text using pdftotext command line tool
     */
    private static function extractWithPdfToText($pdfPath) {
        // Check if pdftotext is available
        $checkCommand = stripos(PHP_OS, 'WIN') === 0
            ? 'where pdftotext'
            : 'which pdftotext';

        exec($checkCommand . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            return false; // pdftotext not available
        }

        // Extract text to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf_') . '.txt';
        $command = 'pdftotext ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($tempFile) . ' 2>&1';

        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($tempFile)) {
            $text = file_get_contents($tempFile);
            unlink($tempFile);
            return self::cleanText($text);
        }

        return false;
    }

    /**
     * Extract text using Smalot PDF Parser library
     * Install via: composer require smalot/pdfparser
     */
    private static function extractWithSmalotParser($pdfPath) {
        $parserPath = __DIR__ . '/../vendor/autoload.php';

        if (!file_exists($parserPath)) {
            return false; // Library not installed
        }

        try {
            require_once $parserPath;

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $text = $pdf->getText();

            return self::cleanText($text);
        } catch (Exception $e) {
            error_log("Smalot PDF Parser error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract text page-by-page using Smalot PDF Parser library
     */
    private static function extractByPageWithSmalot($pdfPath) {
        $parserPath = __DIR__ . '/../vendor/autoload.php';

        if (!file_exists($parserPath)) {
            return false; // Library not installed
        }

        try {
            require_once $parserPath;

            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();

            $pageTexts = [];
            $fullText = '';
            $pageNum = 1;

            foreach ($pages as $page) {
                $pageText = self::cleanText($page->getText());
                $pageTexts[$pageNum] = $pageText;
                $fullText .= $pageText . "\n";
                $pageNum++;
            }

            return [
                'pages' => $pageTexts,
                'full_text' => trim($fullText),
                'page_count' => count($pageTexts)
            ];
        } catch (Exception $e) {
            error_log("Smalot PDF Parser page extraction error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Basic PDF text extraction (limited functionality)
     * Extracts text from simple PDFs without compression
     */
    private static function extractBasic($pdfPath) {
        try {
            $content = file_get_contents($pdfPath);

            if ($content === false) {
                return false;
            }

            // Basic text extraction using regex
            // This works for simple uncompressed PDFs
            $text = '';

            // Extract text between text objects
            if (preg_match_all('/\((.*?)\)/s', $content, $matches)) {
                foreach ($matches[1] as $match) {
                    // Decode PDF string encoding
                    $decoded = self::decodePdfString($match);
                    $text .= $decoded . ' ';
                }
            }

            // Also try to extract from stream objects
            if (preg_match_all('/stream\s*\n(.*?)\nendstream/s', $content, $matches)) {
                foreach ($matches[1] as $stream) {
                    // Try to decompress if it's plain text
                    if (strpos($stream, 'FlateDecode') === false) {
                        $text .= $stream . ' ';
                    }
                }
            }

            $text = self::cleanText($text);

            // If we got meaningful text, return it
            if (strlen(trim($text)) > 50) {
                return $text;
            }

            return false;
        } catch (Exception $e) {
            error_log("Basic PDF extraction error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Decode PDF string encoding
     */
    private static function decodePdfString($string) {
        // Handle octal sequences (\nnn)
        $string = preg_replace_callback('/\\\\(\d{3})/', function($matches) {
            return chr(octdec($matches[1]));
        }, $string);

        // Handle escape sequences
        $string = str_replace([
            '\\n', '\\r', '\\t', '\\b', '\\f',
            '\\(', '\\)', '\\\\'
        ], [
            "\n", "\r", "\t", "\b", "\f",
            '(', ')', '\\'
        ], $string);

        return $string;
    }

    /**
     * Clean and normalize extracted text
     */
    private static function cleanText($text) {
        if (empty($text)) {
            return '';
        }

        // Remove null bytes
        $text = str_replace("\0", '', $text);

        // Convert to UTF-8 if not already
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        // Remove invalid UTF-8 sequences
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Remove control characters except newlines and tabs
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Remove non-printable characters
        $text = preg_replace('/[^\P{C}\n\r\t]/u', '', $text);

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        // Final UTF-8 validation and cleanup
        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);

        return $text;
    }

    /**
     * Check if text extraction is available
     * Returns array with available methods
     */
    public static function checkAvailability() {
        $methods = [];

        // Check pdftotext
        $checkCommand = stripos(PHP_OS, 'WIN') === 0
            ? 'where pdftotext'
            : 'which pdftotext';
        exec($checkCommand . ' 2>&1', $output, $returnCode);
        if ($returnCode === 0) {
            $methods[] = 'pdftotext';
        }

        // Check Smalot library
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            $methods[] = 'smalot';
        }

        // Basic method is always available
        $methods[] = 'basic';

        return $methods;
    }
}
