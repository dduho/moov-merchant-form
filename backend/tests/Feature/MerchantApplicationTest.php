<?php

namespace Tests\Feature;

use App\Models\MerchantApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MerchantApplicationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_merchant_application(): void
    {
        Storage::fake('local');
        
        $response = $this->postJson('/api/merchant-applications', [
            'full_name' => 'Jean Dupont',
            'birth_date' => '1990-01-15',
            'phone' => '+22891234567',
            'email' => 'jean@example.com',
            'address' => '123 Rue de Lomé, Lomé, Togo',
            'id_number' => '1234567890',
            'id_expiry_date' => '2030-12-31',
            'is_foreigner' => false,
            'business_name' => 'Boutique Jean',
            'business_type' => 'boutique',
            'has_cfe' => true,
            'cfe_number' => 'CFE123456',
            'has_nif' => false,
            'latitude' => 6.1319,
            'longitude' => 1.2225,
            'signature' => 'data:image/png;base64,test',
            'accept_terms' => true,
            'documents' => [
                'id_card' => UploadedFile::fake()->image('id_card.jpg')
            ]
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => ['reference_number'],
                     'reference_number'
                 ]);
        
        $this->assertDatabaseHas('merchant_applications', [
            'full_name' => 'Jean Dupont',
            'phone' => '+22891234567'
        ]);
    }
    
    public function test_validation_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/merchant-applications', [
            'full_name' => '',
            'phone' => 'invalid',
        ]);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['full_name', 'phone']);
    }
    
    public function test_can_retrieve_application(): void
    {
        $application = MerchantApplication::factory()->create();
        
        $response = $this->getJson("/api/merchant-applications/{$application->id}");
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => ['id', 'reference_number', 'status']
                 ]);
    }
    
    public function test_rate_limiting_works(): void
    {
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/health');
            
            if ($i < 60) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429);
                break;
            }
        }
    }
}