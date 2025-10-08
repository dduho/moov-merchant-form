<?php

namespace Database\Factories;

use App\Models\ApplicationDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationDocumentFactory extends Factory
{
    protected $model = ApplicationDocument::class;
    
    public function definition(): array
    {
        $types = ['id_card', 'anid_card', 'residence_card'];
        $type = fake()->randomElement($types);
        
        return [
            'document_type' => $type,
            'original_name' => $type . '_' . fake()->uuid() . '.jpg',
            'file_name' => 'test_' . fake()->uuid() . '.jpg',
            'file_path' => 'merchant-documents/' . $type . '/test/' . fake()->uuid() . '.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => fake()->numberBetween(100000, 2000000),
            'upload_ip' => fake()->ipv4(),
            'hash_sha256' => fake()->sha256(),
            'description' => fake()->optional()->sentence(),
            'is_verified' => fake()->boolean(60),
            'verified_at' => fake()->optional(0.6)->dateTimeThisMonth(),
        ];
    }
}