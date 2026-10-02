<?php
defined('BASEPATH') OR define('BASEPATH', TRUE);

$mysqli = new mysqli('localhost', 'root', '', 'db_school');
if ($mysqli->connect_error) {
    die("Database Connection Error: " . $mysqli->connect_error . "\n");
}

echo "Starting Staff Document Settings migration...\n";

// 1. Create tbl_staff_document_types table
$sqlCreateTypes = "
CREATE TABLE IF NOT EXISTS `tbl_staff_document_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` char(1) NOT NULL DEFAULT 'n',
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_is_deleted` (`is_deleted`),
  KEY `idx_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

if ($mysqli->query($sqlCreateTypes)) {
    echo " [OK] Table `tbl_staff_document_types` ensured.\n";
} else {
    echo " [ERR] Error creating `tbl_staff_document_types`: " . $mysqli->error . "\n";
}

// 2. Ensure columns in tbl_staff_documents
$columnsCheck = $mysqli->query("SHOW COLUMNS FROM tbl_staff_documents");
$existingCols = [];
while ($col = $columnsCheck->fetch_assoc()) {
    $existingCols[] = $col['Field'];
}

if (!in_array('document_type_id', $existingCols)) {
    $mysqli->query("ALTER TABLE `tbl_staff_documents` ADD `document_type_id` int(10) unsigned NULL AFTER `staff_id`");
    $mysqli->query("ALTER TABLE `tbl_staff_documents` ADD INDEX `idx_staff_doc_type` (`staff_id`, `document_type_id`, `is_deleted`)");
    echo " [OK] Added `document_type_id` column to `tbl_staff_documents`.\n";
}

if (!in_array('mime_type', $existingCols)) {
    $mysqli->query("ALTER TABLE `tbl_staff_documents` ADD `mime_type` varchar(100) NULL AFTER `file_size`");
    echo " [OK] Added `mime_type` column to `tbl_staff_documents`.\n";
}

// 3. Seed default document types if none exist
$checkTypes = $mysqli->query("SELECT COUNT(*) as cnt FROM tbl_staff_document_types WHERE is_deleted = 'n'");
$typeRow = $checkTypes->fetch_assoc();

if ((int)$typeRow['cnt'] === 0) {
    $defaultTypes = [
        ['document_name' => 'Aadhaar', 'description' => 'Aadhaar Identity Card Copy', 'display_order' => 1],
        ['document_name' => 'PAN', 'description' => 'PAN Card Copy', 'display_order' => 2],
        ['document_name' => 'Experience Certificate', 'description' => 'Previous Employment/Experience Certificate', 'display_order' => 3],
        ['document_name' => 'Education Certificate', 'description' => 'Highest Degree / Qualification Certificate', 'display_order' => 4],
    ];

    $stmt = $mysqli->prepare("INSERT INTO tbl_staff_document_types (document_name, description, status, display_order, created_by, created_at, is_deleted) VALUES (?, ?, 'Active', ?, 1, NOW(), 'n')");
    foreach ($defaultTypes as $dt) {
        $stmt->bind_param("ssi", $dt['document_name'], $dt['description'], $dt['display_order']);
        $stmt->execute();
    }
    $stmt->close();
    echo " [OK] Seeded default staff document types (Aadhaar, PAN, Experience Certificate, Education Certificate).\n";
} else {
    echo " [INFO] Existing staff document types found: " . $typeRow['cnt'] . " active definitions.\n";
}

// 4. Ensure upload directory and .htaccess exist
$uploadDir = dirname(dirname(__DIR__)) . '/uploads/staff_docs';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    echo " [OK] Created upload directory: $uploadDir\n";
}

$htaccessPath = $uploadDir . '/.htaccess';
$htaccessContent = "# Prevent direct execution of script files in uploaded documents folder\n<FilesMatch \"\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|htm|html|shtml|sh|cgi)$\">\n    Order Allow,Deny\n    Deny from all\n</FilesMatch>\n";
file_put_contents($htaccessPath, $htaccessContent);
echo " [OK] Security .htaccess created in $uploadDir\n";

echo "Migration completed successfully!\n";
