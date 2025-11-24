<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MerchantApplication;

echo "=== Vérification Commercial ===\n\n";

// Trouver l'utilisateur commercial
$commercial = User::where('username', 'commercial')->with('roles')->first();

if (!$commercial) {
    echo "❌ Utilisateur commercial non trouvé\n";
    exit;
}

echo "✅ Utilisateur commercial trouvé:\n";
echo "   ID: {$commercial->id}\n";
echo "   Username: {$commercial->username}\n";
echo "   Email: {$commercial->email}\n";
echo "   Rôles: " . $commercial->roles->pluck('name')->join(', ') . "\n";
echo "   Rôle slugs: " . $commercial->roles->pluck('slug')->join(', ') . "\n\n";

// Compter les candidatures
$totalApplications = MerchantApplication::count();
$commercialApplications = MerchantApplication::where('user_id', $commercial->id)->count();

echo "📊 Statistiques candidatures:\n";
echo "   Total candidatures: {$totalApplications}\n";
echo "   Candidatures de commercial (user_id={$commercial->id}): {$commercialApplications}\n\n";

if ($commercialApplications === 0) {
    echo "⚠️  Le commercial n'a aucune candidature assignée\n";
    echo "   Création d'une candidature de test...\n\n";

    $testApp = MerchantApplication::create([
        'user_id' => $commercial->id,
        'reference_number' => 'TEST-COMM-' . time(),
        'full_name' => 'Test Commercial Application',
        'first_name' => 'Test',
        'last_name' => 'Commercial',
        'id_type' => 'cni',
        'id_number' => 'TEST123456',
        'birth_date' => '1990-01-01',
        'birth_place' => 'Lomé',
        'region' => 'Maritime',
        'city' => 'Lomé',
        'business_name' => 'Test Business',
        'business_type' => 'company',
        'phone' => '+22890000001',
        'merchant_phone' => '+22890000001',
        'business_address' => '123 Test Street',
        'email' => 'test@commercial.tg',
        'status' => 'approved',
        'usage_type' => 'merchant',
    ]);

    echo "✅ Candidature de test créée avec ID: {$testApp->id}\n";
    echo "   Référence: {$testApp->reference_number}\n";
} else {
    echo "📋 Liste des candidatures du commercial:\n";
    $apps = MerchantApplication::where('user_id', $commercial->id)->get(['id', 'reference_number', 'status', 'full_name']);
    foreach ($apps as $app) {
        echo "   - ID {$app->id}: {$app->reference_number} - {$app->full_name} ({$app->status})\n";
    }
}

echo "\n✅ Vérification terminée!\n";
