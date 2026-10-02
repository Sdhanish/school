<?php
$views_dir = __DIR__ . '/../application/views';

function scan_dir_recursive($dir) {
    $files = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, scan_dir_recursive($path));
        } elseif (substr($item, -4) === '.php') {
            $files[] = $path;
        }
    }
    return $files;
}

$files = scan_dir_recursive($views_dir);

echo "=== ALL FILES WITH SELECT ELEMENTS RELATED TO GROUP/DEPARTMENT ===\n";
foreach ($files as $f) {
    $content = file_get_contents($f);
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', realpath($f));
    
    preg_match_all('/<select[^>]*>.*?<\/select>/is', $content, $selects);
    foreach ($selects[0] as $sel) {
        if (stripos($sel, 'academic_group') !== false || stripos($sel, 'group_id') !== false || stripos($sel, 'Department / Group') !== false || stripos($sel, 'academic_groups') !== false) {
            preg_match('/<select[^>]*>/i', $sel, $tag);
            echo "$rel => " . ($tag[0] ?? '') . "\n";
        }
    }
}
