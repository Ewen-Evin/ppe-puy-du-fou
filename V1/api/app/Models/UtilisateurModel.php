<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class UtilisateurModel
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM Utilisateur WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM Utilisateur WHERE id_utilisateur = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $email, string $hash, string $nom, string $prenom, string $role = 'visiteur', ?float $vitesse = 4.0): int
    {
        $stmt = Database::get()->prepare(
            'INSERT INTO Utilisateur (email, mot_de_passe, nom, prenom, type_profil, vitesse_marche)
             VALUES (:email, :hash, :nom, :prenom, :role, :vitesse)'
        );
        $stmt->execute([
            ':email' => $email, ':hash' => $hash, ':nom' => $nom,
            ':prenom' => $prenom, ':role' => $role, ':vitesse' => $vitesse,
        ]);
        return (int)Database::get()->lastInsertId();
    }

    public static function updateVitesse(int $id, float $vitesse): void
    {
        $stmt = Database::get()->prepare('UPDATE Utilisateur SET vitesse_marche = :v WHERE id_utilisateur = :id');
        $stmt->execute([':v' => $vitesse, ':id' => $id]);
    }
}
