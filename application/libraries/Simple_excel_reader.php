<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Simple_excel_reader
 *
 * Lightweight, zero-dependency parser for CSV and XLSX files.
 * Uses native PHP functions and ZipArchive/SimpleXML.
 */
class Simple_excel_reader {

    /**
     * Parse an uploaded file (.csv, .xlsx, .xls) into an array of associative rows.
     *
     * @param string $file_path Absolute path to the file
     * @param string|null $orig_filename Original uploaded file name
     * @return array Array of associative rows
     * @throws Exception On parse failure
     */
    public function parse_file($file_path, $orig_filename = null)
    {
        if (!is_file($file_path) || !is_readable($file_path)) {
            throw new Exception('File not found or cannot be read.');
        }

        $filename = $orig_filename ?: $file_path;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            return $this->parse_csv($file_path);
        } elseif ($ext === 'xlsx') {
            return $this->parse_xlsx($file_path);
        } else {
            // Attempt CSV parsing as fallback (e.g. for .xls or plain text tables)
            return $this->parse_csv($file_path);
        }
    }

    /**
     * Parse a CSV file.
     *
     * @param string $file_path
     * @return array
     */
    public function parse_csv($file_path)
    {
        $rows = array();
        $handle = @fopen($file_path, 'r');
        if (!$handle) {
            throw new Exception('Unable to open CSV file.');
        }

        // Detect and remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = null;
        while (($data = fgetcsv($handle, 4096, ",")) !== FALSE) {
            // Handle semicolon-delimited CSVs if only 1 column was parsed and semicolons exist
            if (count($data) === 1 && strpos($data[0], ';') !== FALSE) {
                $data = str_getcsv($data[0], ';');
            }

            // Skip completely empty rows
            $has_content = false;
            foreach ($data as $val) {
                if (trim($val) !== '') {
                    $has_content = true;
                    break;
                }
            }
            if (!$has_content) {
                continue;
            }

            if ($headers === null) {
                $headers = array();
                foreach ($data as $col) {
                    $headers[] = $this->normalize_header($col);
                }
                continue;
            }

            $row = array();
            foreach ($headers as $idx => $header_key) {
                if (!empty($header_key)) {
                    $row[$header_key] = isset($data[$idx]) ? trim($data[$idx]) : '';
                }
            }
            $rows[] = $row;
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Parse an XLSX file using ZipArchive and SimpleXML.
     *
     * @param string $file_path
     * @return array
     */
    public function parse_xlsx($file_path)
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception('PHP ZipArchive extension is required to read .xlsx files.');
        }

        $zip = new ZipArchive();
        if ($zip->open($file_path) !== TRUE) {
            throw new Exception('Unable to open .xlsx archive.');
        }

        // 1. Read shared strings if available
        $shared_strings = array();
        $shared_xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($shared_xml !== FALSE) {
            $xml = @simplexml_load_string($shared_xml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $shared_strings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                        $shared_strings[] = $text;
                    } else {
                        $shared_strings[] = '';
                    }
                }
            }
        }

        // 2. Read first worksheet (xl/worksheets/sheet1.xml)
        $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet_xml === FALSE) {
            // Try looking for any sheet
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('/xl\/worksheets\/sheet\d+\.xml/i', $stat['name'])) {
                    $sheet_xml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if (!$sheet_xml) {
            throw new Exception('No worksheet found inside .xlsx file.');
        }

        $sheet = @simplexml_load_string($sheet_xml);
        if (!$sheet || !isset($sheet->sheetData->row)) {
            throw new Exception('Worksheet contains no data.');
        }

        $raw_grid = array();
        foreach ($sheet->sheetData->row as $rowNode) {
            $row_data = array();
            foreach ($rowNode->c as $cell) {
                $cell_ref = (string)$cell['r'];
                $col_letters = preg_replace('/[0-9]/', '', $cell_ref);
                $col_idx = $this->col_letter_to_number($col_letters) - 1;

                $type = (string)$cell['t'];
                $val = isset($cell->v) ? (string)$cell->v : '';

                if ($type === 's' && isset($shared_strings[(int)$val])) {
                    $cell_value = $shared_strings[(int)$val];
                } elseif ($type === 'inlineStr' && isset($cell->is->t)) {
                    $cell_value = (string)$cell->is->t;
                } else {
                    $cell_value = $val;
                }

                $row_data[$col_idx] = trim($cell_value);
            }

            // Fill gaps in row
            if (!empty($row_data)) {
                $max_idx = max(array_keys($row_data));
                $dense_row = array();
                for ($k = 0; $k <= $max_idx; $k++) {
                    $dense_row[$k] = isset($row_data[$k]) ? $row_data[$k] : '';
                }
                $raw_grid[] = $dense_row;
            }
        }

        if (empty($raw_grid)) {
            return array();
        }

        // Extract headers from row 0
        $headers_raw = array_shift($raw_grid);
        $headers = array();
        foreach ($headers_raw as $col) {
            $headers[] = $this->normalize_header($col);
        }

        $rows = array();
        foreach ($raw_grid as $line) {
            $has_content = false;
            foreach ($line as $v) {
                if (trim($v) !== '') {
                    $has_content = true;
                    break;
                }
            }
            if (!$has_content) continue;

            $row = array();
            foreach ($headers as $idx => $key) {
                if (!empty($key)) {
                    $row[$key] = isset($line[$idx]) ? trim($line[$idx]) : '';
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Convert Excel column letters (A, B, ..., Z, AA, AB) to 1-based index.
     *
     * @param string $col
     * @return int
     */
    private function col_letter_to_number($col)
    {
        $col = strtoupper($col);
        $num = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $num = $num * 26 + (ord($col[$i]) - 64);
        }
        return $num;
    }

    /**
     * Normalize header string to clean snake_case identifier.
     * e.g. "First Name *" -> "first_name", "Admission No." -> "admission_number"
     *
     * @param string $str
     * @return string
     */
    public function normalize_header($str)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^\w\s]/', ' ', $str); // Replace symbols like *, #, ., /, - with space
        $str = trim(preg_replace('/\s+/', '_', $str), '_');

        // Aliases mapping
        $map = array(
            'adm_no'                         => 'admission_number',
            'admission_no'                   => 'admission_number',
            'adm_number'                     => 'admission_number',
            'roll_no'                        => 'roll_number',
            'roll'                           => 'roll_number',
            'dob'                            => 'date_of_birth',
            'birth_date'                     => 'date_of_birth',
            'date_of_birth'                  => 'date_of_birth',
            'fname'                          => 'first_name',
            'mname'                          => 'middle_name',
            'lname'                          => 'last_name',
            'father_name'                    => 'guardian_name',
            'parent_name'                    => 'guardian_name',
            'guardian'                       => 'guardian_name',
            'parent'                         => 'guardian_name',
            'parent_guardian_name'           => 'guardian_name',
            'parentguardian_name'            => 'guardian_name',
            'parent_phone'                   => 'guardian_phone',
            'contact'                        => 'guardian_phone',
            'contact_no'                     => 'guardian_phone',
            'contact_number'                 => 'guardian_phone',
            'phone'                          => 'guardian_phone',
            'phone_no'                       => 'guardian_phone',
            'phone_number'                   => 'guardian_phone',
            'mobile'                         => 'guardian_phone',
            'mobile_no'                      => 'guardian_phone',
            'mobile_number'                  => 'guardian_phone',
            'parent_contact'                 => 'guardian_phone',
            'parent_contact_number'          => 'guardian_phone',
            'guardian_contact'               => 'guardian_phone',
            'guardian_contact_number'        => 'guardian_phone',
            'guardian_phone_number'          => 'guardian_phone',
            'guardian_mobile'                => 'guardian_phone',
            'parent_mobile'                  => 'guardian_phone',
            'parent_guardian_contact'        => 'guardian_phone',
            'parentguardian_contact'         => 'guardian_phone',
            'parent_guardian_contact_number' => 'guardian_phone',
            'parentguardian_contact_number'  => 'guardian_phone',
            'parent_guardian_phone'          => 'guardian_phone',
            'parentguardian_phone'           => 'guardian_phone',
            'parent_guardian_phone_number'   => 'guardian_phone',
            'parentguardian_phone_number'    => 'guardian_phone',
            'parent_email'                   => 'guardian_email',
            'relation'                       => 'guardian_relation',
            'relationship'                   => 'guardian_relation',
            'blood'                          => 'blood_group',
            'pin'                            => 'pincode',
            'pin_code'                       => 'pincode',
            'postal_code'                    => 'pincode'
        );

        return isset($map[$str]) ? $map[$str] : $str;
    }
}
