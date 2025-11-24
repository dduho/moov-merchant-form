<?php

require_once 'backend/vendor/autoload.php';
$app = require_once 'backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ApplicationDocument;

$doc = ApplicationDocument::first();
if ($doc) {
    echo "=== Test du modèle ApplicationDocument ===\n";
    echo "file_size (brut): " . $doc->file_size . "\n";
    echo "formatted_size: " . $doc->formatted_size . "\n";
    echo "getTypeLabel(): " . $doc->getTypeLabel() . "\n";
    echo "\n";
    
    // Test de la resource
    echo "=== Test de la ressource ===\n";
    $resource = new \App\Http\Resources\ApplicationDocumentResource($doc);
    $array = $resource->toArray(new \Illuminate\Http\Request());
    echo "file_size dans resource: " . $array['file_size'] . "\n";
    echo "size dans resource: " . $array['size'] . "\n";
} else {
    echo "Aucun document trouvé\n";
}