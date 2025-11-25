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
            'is_read' => $this->isRead(), // Utiliser la méthode du modèle
            'read_at' => $this->read_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'data' => $this->data,
            'action_url' => $this->action_url,
            'is_expired' => $this->isExpired(),
            'formatted_time' => $this->created_at->diffForHumans(),
            'time_ago' => $this->created_at->diffForHumans(),
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
            'new_application' => 'document-plus',
            'application_submitted' => 'check-circle',
            'application_approved' => 'check-circle',
            'application_rejected' => 'x-circle',
            'personal_notification' => 'chat-bubble-left-right',
            'objective_assigned' => 'target',
            'objective_updated' => 'chart-bar',
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
            'new_application' => 'blue',
            'application_submitted' => 'green',
            'application_approved' => 'green',
            'application_rejected' => 'red',
            'personal_notification' => 'indigo',
            'objective_assigned' => 'orange',
            'objective_updated' => 'purple',
            'document_verified' => 'blue',
            'system' => 'gray',
            'reminder' => 'yellow',
            default => 'blue'
        };
    }
}