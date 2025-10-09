<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMerchantApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Informations personnelles
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'birth_place' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
            'nationality' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'merchant_phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'region' => 'required|in:Maritime,Plateaux,Centrale,Kara,Savanes',
            
            // Documents d'identité
            'id_type' => 'required|in:cni,passport,residence,elector,driving_license,foreign_id',
            'id_number' => 'required|string|max:50',
            'id_expiry_date' => 'required|date|after:today',
            'has_anid_card' => 'nullable|boolean',
            'anid_number' => 'nullable|string|max:50',
            'anid_expiry_date' => 'nullable|date|after:today',
            'is_foreigner' => 'nullable|boolean',
            
            // Informations commerciales
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:boutique,pharmacie,station-service,supermarche,autre',
            'business_phones' => 'nullable|array',
            'business_phones.*' => 'nullable|string|max:20',
            'business_email' => 'nullable|email|max:255',
            'business_address' => 'required|string',
            'city' => 'required|string|max:255',
            'usage_type' => 'required|in:TRADER,MERC,TRADERWNIF,CORP',
            'has_cfe' => 'nullable|boolean',
            'cfe_number' => 'required_if:has_cfe,true|nullable|string|max:50',
            'cfe_expiry_date' => 'required_if:has_cfe,true|nullable|date|after:today',
            'has_nif' => 'nullable|boolean',
            'nif_number' => 'required_if:has_nif,true|nullable|string|max:50',
            
            // Localisation
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_accuracy' => 'nullable|numeric',
            'location_description' => 'nullable|string',
            'shop_address' => 'nullable|string',
            'shop_city' => 'nullable|string|max:255',
            
            // Signature
            'signature' => 'required|string',
            'accept_terms' => 'required|accepted',
            
            // Documents (fichiers) - Individual fields
            'id_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'anid_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cfe_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'business_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'residence_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'residence_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nif_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Legacy documents array (for backward compatibility)
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire',
            'last_name.required' => 'Le nom est obligatoire',
            'birth_place.required' => 'Le lieu de naissance est obligatoire',
            'gender.required' => 'Le sexe est obligatoire',
            'gender.in' => 'Le sexe doit être M ou F',
            'nationality.required' => 'La nationalité est obligatoire',
            'phone.required' => 'Le numéro de téléphone est obligatoire',
            'merchant_phone.required' => 'Le téléphone marchand est obligatoire',
            'birth_date.before' => 'La date de naissance doit être antérieure à aujourd\'hui',
            'id_type.required' => 'Le type de pièce d\'identité est obligatoire',
            'id_type.in' => 'Type de pièce invalide',
            'id_expiry_date.after' => 'La pièce d\'identité est expirée',
            'anid_expiry_date.after' => 'La carte ANID est expirée',
            'cfe_number.required_if' => 'Le numéro CFE est obligatoire si vous possédez une carte CFE',
            'cfe_expiry_date.required_if' => 'La date d\'expiration CFE est obligatoire si vous possédez une carte CFE',
            'cfe_expiry_date.after' => 'La carte CFE est expirée',
            'nif_number.required_if' => 'Le numéro NIF est obligatoire si vous possédez un numéro NIF',
            'business_name.required' => 'Le nom du commerce est obligatoire',
            'business_type.required' => 'Le type d\'activité est obligatoire',
            'business_type.in' => 'Type d\'activité invalide',

            'business_address.required' => 'L\'adresse du commerce est obligatoire',
            'city.required' => 'La ville/village est obligatoire',
            'region.required' => 'La région est obligatoire',
            'region.in' => 'Veuillez sélectionner une région valide (Maritime, Plateaux, Centrale, Kara, Savanes)',
            'usage_type.required' => 'Le type d\'utilisation est obligatoire',
            'usage_type.in' => 'Type d\'utilisation invalide (TRADER, MERC, TRADERWNIF, CORP)',
            'signature.required' => 'La signature est obligatoire',
            'accept_terms.accepted' => 'Vous devez accepter les conditions générales',
            // Document validation messages
            'id_card.mimes' => 'La pièce d\'identité doit être un fichier PDF, JPG ou PNG',
            'id_card.max' => 'La pièce d\'identité ne doit pas dépasser 5MB',
            'anid_card.mimes' => 'La carte ANID doit être un fichier PDF, JPG ou PNG',
            'anid_card.max' => 'La carte ANID ne doit pas dépasser 5MB',
            'cfe_document.mimes' => 'Le document CFE doit être un fichier PDF, JPG ou PNG',
            'cfe_document.max' => 'Le document CFE ne doit pas dépasser 5MB',
            'business_document.mimes' => 'Le document commercial doit être un fichier PDF, JPG ou PNG',
            'business_document.max' => 'Le document commercial ne doit pas dépasser 5MB',
            'residence_card.mimes' => 'La carte de résidence doit être un fichier PDF, JPG ou PNG',
            'residence_card.max' => 'La carte de résidence ne doit pas dépasser 5MB',
            'residence_proof.mimes' => 'La preuve de résidence doit être un fichier PDF, JPG ou PNG',
            'residence_proof.max' => 'La preuve de résidence ne doit pas dépasser 5MB',
            'nif_document.mimes' => 'Le document NIF doit être un fichier PDF, JPG ou PNG',
            'nif_document.max' => 'Le document NIF ne doit pas dépasser 5MB',
            
            // Legacy documents array messages
            'documents.*.mimes' => 'Le document doit être un fichier PDF, JPG ou PNG',
            'documents.*.max' => 'Le fichier ne doit pas dépasser 5MB',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir les valeurs booléennes
        $booleans = ['has_anid_card', 'is_foreigner', 'has_cfe', 'has_nif', 'accept_terms'];
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->$field, FILTER_VALIDATE_BOOLEAN)
                ]);
            }
        }

        // Générer full_name à partir de first_name et last_name
        if ($this->has('first_name') && $this->has('last_name')) {
            $this->merge([
                'full_name' => trim($this->first_name . ' ' . $this->last_name)
            ]);
        }
    }

    /**
     * Obtenir les règles de validation pour une mise à jour avec l'ID de l'application
     */
    public static function rulesFor(?int $applicationId = null): array
    {
        return [
            // Informations personnelles
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'required|date|before:today',
            'birth_place' => 'required|string|max:255',
            'gender' => 'required|in:M,F',
            'nationality' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'merchant_phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            
            // Documents d'identité
            'id_type' => 'required|in:cni,passport,elector,driving_license,foreign_id',
            'id_number' => 'required|string|max:50',
            'id_expiry_date' => 'required|date|after:today',
            'has_anid_card' => 'nullable|boolean',
            'anid_number' => 'nullable|string|max:50',
            'anid_expiry_date' => 'nullable|date|after:today',
            'is_foreigner' => 'nullable|boolean',
            
            // Informations commerciales
            'business_name' => 'required|string|max:255',
            'business_type' => 'required|in:boutique,pharmacie,station-service,supermarche,autre',
            'business_phones' => 'nullable|array',
            'business_phones.*' => 'nullable|string|max:20',
            'business_email' => 'nullable|email|max:255',
            'business_address' => 'required|string',
            'usage_type' => 'required|in:TRADER,MERC,TRADERWNIF,CORP',
            'has_cfe' => 'nullable|boolean',
            'cfe_number' => 'required_if:has_cfe,true|nullable|string|max:50',
            'cfe_expiry_date' => 'required_if:has_cfe,true|nullable|date|after:today',
            'has_nif' => 'nullable|boolean',
            'nif_number' => 'required_if:has_nif,true|nullable|string|max:50',
            
            // Localisation
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_accuracy' => 'nullable|numeric',
            'location_description' => 'nullable|string',
            'shop_address' => 'nullable|string',
            'shop_city' => 'nullable|string|max:255',
            
            // Signature
            'signature' => 'required|string',
            'accept_terms' => 'required|accepted',
            
            // Documents (fichiers) - Individual fields
            'id_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'anid_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cfe_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'business_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'residence_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'residence_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nif_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Legacy documents array (for backward compatibility)
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }
}