<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->view('auth/login', ['error' => null]);
    }

    public function login(): void
    {
        $this->checkCsrf();
        $email = trim($_POST['email'] ?? '');
        $pwd   = $_POST['mot_de_passe'] ?? '';

        $resp = $this->api->post('/api/auth/login', [
            'email'        => $email,
            'mot_de_passe' => $pwd,
        ]);

        if (($resp['_status'] ?? 0) !== 200 || empty($resp['token'])) {
            $this->view('auth/login', ['error' => $resp['error'] ?? 'Connexion impossible']);
            return;
        }
        if (($resp['user']['type_profil'] ?? '') !== 'gestionnaire') {
            $this->view('auth/login', ['error' => 'Accès réservé aux gestionnaires']);
            return;
        }
        $_SESSION['token'] = $resp['token'];
        $_SESSION['user']  = $resp['user'];
        $this->redirect('/', 'Bienvenue ' . $resp['user']['prenom']);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}
