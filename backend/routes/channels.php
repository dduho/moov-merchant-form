<?php

use Illuminate\Support\Facades\Broadcast;

// Exemple de channel pour notifications en temps réel (optionnel)
Broadcast::channel('applications.{id}', function ($user, $id) {
    return true; // Ajouter logique d'autorisation
});