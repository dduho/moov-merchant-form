<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->document_type,
            'type_label' => $this->getDocumentTypeLabel($this->document_type), // Frontend expects this
            'original_name' => $this->original_name,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'url' => $this->url,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'size' => $this->formatFileSize($this->file_size), // Frontend expects 'size'
            'upload_ip' => $this->upload_ip,
            'hash_sha256' => $this->hash_sha256,
            'hash_md5' => $this->hash_md5,
            'description' => $this->description,
            'is_verified' => $this->is_verified,
            'verified' => $this->is_verified, // Frontend expects 'verified'
            'verified_at' => $this->verified_at?->format('Y-m-d H:i:s'),
            'verified_by' => $this->verified_by,
            'verification_notes' => $this->verification_notes,
            'metadata' => $this->metadata,
            'merchant_application_id' => $this->merchant_application_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'verifier' => new UserResource($this->whenLoaded('verifier')),
        ];
    }

    private function getDocumentTypeLabel(string $type): string
    {
        return match($type) {
            'id_card' => 'Pièce d\'identité',
            'passport' => 'Passeport',
            'residence_permit' => 'Titre de séjour',
            'business_permit' => 'Autorisation commerciale',
            'cfe_certificate' => 'Certificat CFE',
            'nif_certificate' => 'Certificat NIF',
            'tax_certificate' => 'Certificat fiscal',
            'photo' => 'Photo',
            'signature' => 'Signature',
            'other' => 'Autre document',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }

    private function formatFileSize(?int $bytes): string
    {
        if (!$bytes) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 1) . ' ' . $units[$pow];
    }
}