<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'document_type' => $this->document_type,
            'type_label' => $this->getTypeLabel(),
            'original_name' => $this->original_name,
            'file_size' => $this->formatted_size, // Taille formatée (ex: "1.2 MB")
            'size' => $this->file_size, // Taille en bytes pour le calcul frontend
            'mime_type' => $this->mime_type,
            'url' => $this->url,
            'verified' => $this->is_verified,
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at?->toISOString(),
            'uploaded_at' => $this->created_at?->toISOString(),
        ];
    }
}