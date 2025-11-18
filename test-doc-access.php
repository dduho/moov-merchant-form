<?php
require '/var/www/moov-merchant-form/backend/vendor/autoload.php';
$app = require_once '/var/www/moov-merchant-form/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$doc = App\Models\ApplicationDocument::find(2);
if ($doc) {
    echo "Document trouvé:\n";
    echo "  ID: " . $doc->id . "\n";
    echo "  File path: " . $doc->file_path . "\n";
    echo "  File exists: " . (Storage::exists($doc->file_path) ? 'OUI' : 'NON') . "\n";
    echo "  File size DB: " . $doc->file_size . " bytes\n";
    
    if (Storage::exists($doc->file_path)) {
        $realSize = Storage::size($doc->file_path);
        echo "  File size réel: " . $realSize . " bytes\n";
        echo "  Match: " . ($doc->file_size == $realSize ? 'OUI' : 'NON - PROBLÈME!') . "\n";
        
        $fullPath = storage_path('app/' . $doc->file_path);
        echo "  Full path: " . $fullPath . "\n";
        echo "  Readable: " . (is_readable($fullPath) ? 'OUI' : 'NON') . "\n";
        
        if (file_exists($fullPath)) {
            $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
            $owner = posix_getpwuid(fileowner($fullPath));
            echo "  Permissions: " . $perms . "\n";
            echo "  Owner: " . $owner['name'] . "\n";
        }
    }
} else {
    echo "Document ID 2 introuvable\n";
}
