<?php
$baseDir = '/var/www/moov-merchant-form/backend/storage/app/private';
echo "Contenu de $baseDir:\n";
echo str_repeat('=', 60) . "\n";

function listDirectory($dir, $indent = 0) {
    if (!is_dir($dir)) {
        echo "  " . str_repeat('  ', $indent) . "[NOT A DIRECTORY]\n";
        return;
    }
    
    $items = @scandir($dir);
    if ($items === false) {
        echo "  " . str_repeat('  ', $indent) . "[PERMISSION DENIED]\n";
        return;
    }
    
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = $dir . '/' . $item;
        $perms = substr(sprintf('%o', @fileperms($path)), -4);
        $size = is_file($path) ? filesize($path) : 0;
        
        if (is_dir($path)) {
            echo str_repeat('  ', $indent) . " $item/ ($perms)\n";
            if ($indent < 5) {
                listDirectory($path, $indent + 1);
            }
        } else {
            echo str_repeat('  ', $indent) . " $item ($perms, " . number_format($size) . " bytes)\n";
        }
    }
}

listDirectory($baseDir);
