<?php

// Test de sécurité des notifications
// Script pour valider que les commerciaux ne voient que leurs propres notifications

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Notification;
use App\Models\MerchantApplication;
use App\Services\NotificationService;

echo "🔒 Test de sécurité des notifications\n";
echo "=====================================\n\n";

// Simuler la connexion d'un commercial
$commercial1 = User::whereHas('roles', function($q) {
    $q->where('slug', 'commercial');
})->first();

$commercial2 = User::whereHas('roles', function($q) {
    $q->where('slug', 'commercial');
})->where('id', '!=', $commercial1->id ?? 0)->first();

$admin = User::whereHas('roles', function($q) {
    $q->where('slug', 'admin');
})->first();

if (!$commercial1 || !$commercial2 || !$admin) {
    echo "❌ Impossible de trouver les utilisateurs de test nécessaires\n";
    echo "Il faut au moins 2 commerciaux et 1 admin dans la base\n";
    exit(1);
}

echo "👥 Utilisateurs de test:\n";
echo "Commercial 1: {$commercial1->first_name} {$commercial1->last_name} (ID: {$commercial1->id})\n";
echo "Commercial 2: {$commercial2->first_name} {$commercial2->last_name} (ID: {$commercial2->id})\n";
echo "Admin: {$admin->first_name} {$admin->last_name} (ID: {$admin->id})\n\n";

// Créer des notifications test
$notificationService = new NotificationService();

// Notification pour commercial 1
$notif1 = $notificationService->createNotification(
    $commercial1,
    'application_submitted',
    'Test - Votre candidature',
    'Test notification pour commercial 1',
    ['application_id' => 999],
    '/applications/999'
);

// Notification pour commercial 2
$notif2 = $notificationService->createNotification(
    $commercial2,
    'application_approved',
    'Test - Candidature approuvée',
    'Test notification pour commercial 2',
    ['application_id' => 998],
    '/applications/998'
);

// Notification pour admin
$notif3 = $notificationService->createNotification(
    $admin,
    'new_application',
    'Test - Nouvelle candidature admin',
    'Test notification pour admin',
    ['application_id' => 997],
    '/applications/997'
);

echo "📝 Notifications créées:\n";
echo "- Notification {$notif1->id} pour Commercial 1\n";
echo "- Notification {$notif2->id} pour Commercial 2\n";
echo "- Notification {$notif3->id} pour Admin\n\n";

// Test 1: Commercial 1 ne doit voir que ses notifications
echo "🔍 Test 1: Notifications du Commercial 1\n";
$notificationsCommercial1 = $notificationService->getAllNotifications($commercial1, 50);

$foundOwnNotification = false;
$foundOtherNotification = false;

foreach ($notificationsCommercial1 as $notif) {
    if ($notif['id'] == $notif1->id) {
        $foundOwnNotification = true;
        echo "✅ Commercial 1 voit sa propre notification (ID: {$notif['id']})\n";
    } elseif ($notif['id'] == $notif2->id || $notif['id'] == $notif3->id) {
        $foundOtherNotification = true;
        echo "❌ ERREUR: Commercial 1 voit une notification qui ne lui appartient pas (ID: {$notif['id']})\n";
    }
}

if (!$foundOwnNotification) {
    echo "❌ ERREUR: Commercial 1 ne voit pas sa propre notification\n";
}

if (!$foundOtherNotification) {
    echo "✅ Commercial 1 ne voit aucune notification d'autres utilisateurs\n";
}

echo "\n";

// Test 2: Commercial 2 ne doit voir que ses notifications
echo "🔍 Test 2: Notifications du Commercial 2\n";
$notificationsCommercial2 = $notificationService->getAllNotifications($commercial2, 50);

$foundOwnNotification2 = false;
$foundOtherNotification2 = false;

foreach ($notificationsCommercial2 as $notif) {
    if ($notif['id'] == $notif2->id) {
        $foundOwnNotification2 = true;
        echo "✅ Commercial 2 voit sa propre notification (ID: {$notif['id']})\n";
    } elseif ($notif['id'] == $notif1->id || $notif['id'] == $notif3->id) {
        $foundOtherNotification2 = true;
        echo "❌ ERREUR: Commercial 2 voit une notification qui ne lui appartient pas (ID: {$notif['id']})\n";
    }
}

if (!$foundOwnNotification2) {
    echo "❌ ERREUR: Commercial 2 ne voit pas sa propre notification\n";
}

if (!$foundOtherNotification2) {
    echo "✅ Commercial 2 ne voit aucune notification d'autres utilisateurs\n";
}

echo "\n";

// Test 3: Admin doit voir toutes ses notifications
echo "🔍 Test 3: Notifications de l'Admin\n";
$notificationsAdmin = $notificationService->getAllNotifications($admin, 50);

$foundAdminNotification = false;

foreach ($notificationsAdmin as $notif) {
    if ($notif['id'] == $notif3->id) {
        $foundAdminNotification = true;
        echo "✅ Admin voit sa propre notification (ID: {$notif['id']})\n";
    }
}

if (!$foundAdminNotification) {
    echo "❌ ERREUR: Admin ne voit pas sa propre notification\n";
}

// Nettoyage
echo "\n🧹 Nettoyage des notifications de test...\n";
$notif1->delete();
$notif2->delete();
$notif3->delete();
echo "✅ Notifications de test supprimées\n";

echo "\n🏁 Test de sécurité terminé\n";

if (!$foundOtherNotification && !$foundOtherNotification2 && $foundOwnNotification && $foundOwnNotification2 && $foundAdminNotification) {
    echo "✅ SUCCÈS: Tous les tests de sécurité sont passés!\n";
    echo "Les commerciaux ne voient que leurs propres notifications.\n";
} else {
    echo "❌ ÉCHEC: Certains tests de sécurité ont échoué!\n";
    echo "Il y a des fuites de notifications entre utilisateurs.\n";
}