<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Jwt;
use App\Models\UtilisateurModel;

final class AuthController extends Controller
{
    public function register(array $ctx): array
    {
        $d = $this->input();
        if ($missing = $this->require($d, ['email', 'mot_de_passe', 'nom', 'prenom'])) {
            return $this->error('Champs manquants : ' . implode(',', $missing), 422);
        }
        if (UtilisateurModel::findByEmail($d['email'])) {
            return $this->error('Email déjà utilisé', 409);
        }
        $hash = password_hash($d['mot_de_passe'], PASSWORD_BCRYPT);
        $id = UtilisateurModel::create(
            $d['email'], $hash, $d['nom'], $d['prenom'],
            'visiteur', isset($d['vitesse_marche']) ? (float)$d['vitesse_marche'] : 4.0
        );
        return $this->json(['id_utilisateur' => $id], 201);
    }

    public function login(array $ctx): array
    {
        $d = $this->input();
        if ($missing = $this->require($d, ['email', 'mot_de_passe'])) {
            return $this->error('Champs manquants', 422);
        }
        $user = UtilisateurModel::findByEmail($d['email']);
        if (!$user || !password_verify($d['mot_de_passe'], $user['mot_de_passe'])) {
            return $this->error('Identifiants invalides', 401);
        }
        $token = Jwt::encode([
            'sub'  => (int)$user['id_utilisateur'],
            'role' => $user['type_profil'],
            'name' => $user['prenom'] . ' ' . $user['nom'],
        ]);
        return $this->json([
            'token' => $token,
            'user'  => [
                'id_utilisateur' => (int)$user['id_utilisateur'],
                'email'          => $user['email'],
                'nom'            => $user['nom'],
                'prenom'         => $user['prenom'],
                'type_profil'    => $user['type_profil'],
                'vitesse_marche' => (float)$user['vitesse_marche'],
            ],
        ]);
    }

    public function me(array $ctx): array
    {
        $user = UtilisateurModel::findById((int)$ctx['user']['sub']);
        if (!$user) {
            return $this->error('Introuvable', 404);
        }
        unset($user['mot_de_passe']);
        return $this->json($user);
    }

    public function updateVitesse(array $ctx): array
    {
        $d = $this->input();
        if (!isset($d['vitesse_marche'])) {
            return $this->error('vitesse_marche requise', 422);
        }
        UtilisateurModel::updateVitesse((int)$ctx['user']['sub'], (float)$d['vitesse_marche']);
        return $this->json(['ok' => true]);
    }
}
