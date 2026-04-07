<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SpectacleModel
{
    public static function all(): array
    {
        return Database::get()->query(
            'SELECT s.*, l.nom AS lieu_nom, l.coordonnees_gps
             FROM Spectacle s JOIN Lieu l ON l.id_lieu = s.id_lieu
             ORDER BY s.libelle'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT s.*, l.nom AS lieu_nom, l.coordonnees_gps
             FROM Spectacle s JOIN Lieu l ON l.id_lieu = s.id_lieu
             WHERE s.id_spectacle = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $d): int
    {
        $stmt = Database::get()->prepare(
            'INSERT INTO Spectacle (libelle, description, duree_spectacle, duree_attente, id_lieu)
             VALUES (:libelle, :description, :duree, :attente, :id_lieu)'
        );
        $stmt->execute([
            ':libelle' => $d['libelle'],
            ':description' => $d['description'] ?? null,
            ':duree' => $d['duree_spectacle'],
            ':attente' => $d['duree_attente'] ?? '00:00:00',
            ':id_lieu' => (int)$d['id_lieu'],
        ]);
        return (int)Database::get()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $stmt = Database::get()->prepare(
            'UPDATE Spectacle SET libelle=:libelle, description=:description,
                duree_spectacle=:duree, duree_attente=:attente, id_lieu=:id_lieu
             WHERE id_spectacle=:id'
        );
        $stmt->execute([
            ':libelle' => $d['libelle'],
            ':description' => $d['description'] ?? null,
            ':duree' => $d['duree_spectacle'],
            ':attente' => $d['duree_attente'] ?? '00:00:00',
            ':id_lieu' => (int)$d['id_lieu'],
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = Database::get()->prepare('DELETE FROM Spectacle WHERE id_spectacle = :id');
        $stmt->execute([':id' => $id]);
    }
}
