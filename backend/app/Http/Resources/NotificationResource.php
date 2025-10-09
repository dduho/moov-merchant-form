<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'priority' => $this->priority,
            'is_read' => $this->is_read,
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'data' => $this->data,
            'is_expired' => $this->isExpired(),
            'formatted_time' => $this->created_at->diffForHumans(),
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
        ];
    }

    /**
     * Obtenir l'icône basée sur le type
     */
    private function getIcon(): string
    {
        return match($this->type) {
            'application_approved' => 'check-circle',
            'application_rejected' => 'x-circle',
            'document_verified' => 'shield-check',
            'system' => 'info',
            'reminder' => 'bell',
            default => 'bell'
        };
    }

    /**
     * Obtenir la couleur basée sur le type et la priorité
     */
    private function getColor(): string
    {
        if ($this->priority === 'high') {
            return 'red';
        }
        
        return match($this->type) {
            'application_approved' => 'green',
            'application_rejected' => 'red',
            'document_verified' => 'blue',
            'system' => 'gray',
            'reminder' => 'yellow',
            default => 'blue'
        };
    }
}