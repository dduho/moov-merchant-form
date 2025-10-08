<?php

require_once 'backend/vendor/autoload.php';

// Test if Intervention Image can be loaded
try {
    $app = require_once 'backend/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "Testing Intervention Image...\n";
    
    // Try to use ImageManager directly (v3 approach)
    $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
    $image = $manager->read('data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    
    echo "✓ Intervention Image v3 is working!\n";
    echo "Image width: " . $image->width() . "\n";
    echo "Image height: " . $image->height() . "\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}