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
            'original_name' => $this->original_name,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'url' => $this->url,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'upload_ip' => $this->upload_ip,
            'hash_sha256' => $this->hash_sha256,
            'hash_md5' => $this->hash_md5,
            'description' => $this->description,
            'is_verified' => $this->is_verified,
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
}