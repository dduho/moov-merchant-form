<?php

namespace Database\Factories;

use App\Models\MerchantApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantApplicationFactory extends Factory
{
    protected $model = MerchantApplication::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        
        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => "$firstName $lastName",
            'birth_date' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'birth_place' => $this->faker->city(),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'nationality' => 'Togolaise',
            'phone' => '+228' . $this->faker->numerify('########'),
            'merchant_phone' => '+228' . $this->faker->numerify('########'),
            'email' => $this->faker->optional(0.7)->safeEmail(),
            'address' => $this->faker->address(),
            'id_type' => $this->faker->randomElement(['cni', 'passport', 'residence']),
            'id_number' => $this->faker->unique()->numerify('##########'),
            'id_expiry_date' => $this->faker->dateTimeBetween('now', '+10 years'),
            'has_anid_card' => (bool) $this->faker->boolean(30),
            'anid_number' => $this->faker->optional(0.3)->numerify('##########'),
            'is_foreigner' => (bool) $this->faker->boolean(10),
            'business_name' => $this->faker->company(),
            'business_type' => $this->faker->randomElement(['boutique', 'pharmacie', 'station-service', 'supermarche', 'autre']),
            'business_phones' => json_encode(['+228' . $this->faker->numerify('########')]),
            'business_email' => $this->faker->companyEmail(),
            'business_address' => $this->faker->address(),
            'usage_type' => $this->faker->randomElement(['TRADER', 'MERC', 'TRADERWNIF', 'CORP']),
            'has_cfe' => (bool) $this->faker->boolean(60),
            'cfe_number' => $this->faker->optional(0.6)->numerify('CFE########'),
            'has_nif' => (bool) $this->faker->boolean(40),
            'nif_number' => $this->faker->optional(0.4)->numerify('NIF########'),
            'latitude' => $this->faker->latitude(5.5, 6.5),
            'longitude' => $this->faker->longitude(0.5, 1.8),
            'location_accuracy' => $this->faker->numberBetween(5, 50),
            'location_description' => $this->faker->optional()->sentence(),
            // Une image “base64 minimale” mais valide
            'signature' => 'data:image/png;base64,' . base64_encode('signature'),
            'accept_terms' => true,
            'status' => $this->faker->randomElement(['pending', 'under_review', 'approved', 'rejected']),
            'admin_notes' => $this->faker->optional(0.3)->sentence(),
            'submitted_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'reviewed_at' => $this->faker->optional(0.5)->dateTimeBetween('-20 days', 'now'),
        ];
    }
}
