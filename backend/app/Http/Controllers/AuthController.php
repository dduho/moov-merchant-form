<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt([
            'username' => $request->username,
            'password' => $request->password,
            'is_active' => true
        ], true)) { // Le deuxième paramètre "true" active le "remember me"
            throw ValidationException::withMessages([
                'username' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur peut se connecter
        if (!$user->canLogin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'username' => ['Votre compte est bloqué. Contactez l\'administrateur.'],
            ]);
        }

        // Enregistrer la dernière connexion
        $user->last_login_at = now();
        $user->save();

        $request->session()->regenerate();

        $userData = $user->toArray();
        $userData['roles'] = $user->roles->pluck('slug');

        return response()->json([
            'user' => $userData,
            'must_change_password' => $user->must_change_password
        ]);
    }

    public function register(Request $request)
    {
        // Vérifier que l'utilisateur courant est un admin (si connecté)
        if (Auth::check() && !Auth::user()->hasRole('admin')) {
            return response()->json([
                'message' => 'Accès refusé',
                'errors' => ['permission' => ['Vous devez être administrateur pour créer un utilisateur']]
            ], 403);
        }

        // Si pas connecté, on refuse l'inscription (création réservée aux admins)
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Non authentifié',
                'errors' => ['auth' => ['Vous devez être connecté en tant qu\'admin pour créer un utilisateur']]
            ], 401);
        }

        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:users',
                'phone' => 'required|string|unique:users',
                'username' => 'required|string|unique:users|min:4',
                'password' => 'required|string|min:6',
                'role_slug' => ['required', Rule::in(['admin', 'commercial', 'personnel'])],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation des données',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'is_active' => true,
                'must_change_password' => true
            ]);

            // Assigner le rôle
            $role = Role::where('slug', $request->role_slug)->first();
            if (!$role) {
                return response()->json([
                    'message' => 'Erreur de création',
                    'errors' => ['role' => ["Le rôle '{$request->role_slug}' n'existe pas"]]
                ], 400);
            }
            
            $user->roles()->attach($role->id);

            // Plus besoin de créer des objectifs particuliers automatiquement
            // Les objectifs globaux s'appliquent à tous les commerciaux

            return response()->json([
                'message' => 'Utilisateur créé avec succès',
                'user' => $user->load('roles'),
                'success' => true
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'errors' => ['server' => [$e->getMessage()]]
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $userData = $user->toArray();
        $userData['roles'] = $user->roles->pluck('slug');
        return response()->json($userData);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'nullable|string|min:6',
        ]);

        if ($request->current_password && !Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->new_password ? Hash::make($request->new_password) : $user->password,
        ]);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }

    public function changePasswordRequired(Request $request)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur doit changer son mot de passe
        if (!$user->must_change_password) {
            return response()->json(['message' => 'Changement de mot de passe non requis'], 400);
        }

        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user->password = Hash::make($request->new_password);
        $user->markPasswordAsChanged();

        return response()->json([
            'message' => 'Mot de passe changé avec succès',
            'data' => [
                'must_change_password' => false
            ]
        ]);
    }
}