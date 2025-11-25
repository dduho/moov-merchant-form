<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\UserResource;

class MerchantApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            
            // Informations personnelles
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'birth_place' => $this->birth_place,
            'gender' => $this->gender,
            'gender_label' => $this->gender === 'M' ? 'Masculin' : 'Féminin',
            'nationality' => $this->nationality,
            'phone' => $this->phone,
            'merchant_phone' => $this->merchant_phone,
            'email' => $this->email,
            'address' => $this->address,
            'region' => $this->region,
            'city' => $this->city,
            
            // Documents d'identité
            'id_type' => $this->id_type,
            'id_type_label' => $this->id_type_label,
            'id_number' => $this->id_number,
            'id_expiry_date' => $this->id_expiry_date?->format('Y-m-d'),
            // Legacy ANID columns removed: represent ANID using id_type === 'carte_anid'
            'has_anid_card' => (($this->id_type ?? null) === 'carte_anid'),
            'anid_number' => (($this->id_type ?? null) === 'carte_anid') ? $this->id_number : null,
            'anid_expiry_date' => (($this->id_type ?? null) === 'carte_anid') ? $this->id_expiry_date?->format('Y-m-d') : null,
            'is_foreigner' => $this->is_foreigner,
            
            // Informations commerciales
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'business_type_label' => $this->business_type_label,
            'business_phone' => $this->phone, // Utilise la colonne 'phone' au lieu de 'business_phone'
            'phone' => $this->phone,
            'business_email' => $this->business_email,
            'business_address' => $this->business_address,
            'usage_type' => $this->usage_type,
            'usage_type_label' => $this->usage_type_label,
            'has_cfe' => $this->has_cfe,
            'cfe_number' => $this->cfe_number,
            'cfe_expiry_date' => $this->cfe_expiry_date?->format('Y-m-d'),
            'has_nif' => $this->has_nif,
            'nif_number' => $this->nif_number,
            
            // Localisation
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location_accuracy' => $this->location_accuracy,
            'location_description' => $this->location_description,
            'shop_address' => $this->shop_address,
            'shop_city' => $this->shop_city,
            
            // Signature
            'signature' => $this->signature,
            
            // Statut
            'status' => $this->status,
            'status_label' => $this->status_label,
            'admin_notes' => $this->admin_notes,
            'user_id' => $this->user_id, // Ajout pour debug
            
            // Dates
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            
            // Relations
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'commercial' => new UserResource($this->whenLoaded('user')),
            
            // Métadonnées
            'has_required_documents' => $this->hasRequiredDocuments(),
        ];
    }
}