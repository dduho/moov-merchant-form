<?php

// Créer un formulaire de test avec une erreur volontaire
$invalidData = json_encode([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'phone' => '1234567890',
    'email' => 'john@example.com',
    'business_name' => 'Test Business',
    'business_type' => 'invalid_type', // Cette valeur causera une erreur de validation
    'id_number' => '123456789',
    'address' => 'Test Address'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/merchant-applications');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $invalidData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

if ($httpCode === 422) {
    $data = json_decode($response, true);
    echo "Validation error detected:\n";
    print_r($data['errors']);
}