<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class LieuModel
{
    public static function all(): array
    {
        return Database::get()->query('SELECT * FROM Lieu ORDER BY nom')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $s = Database::get()->prepare('SELECT * FROM Lieu WHERE id_lieu = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $s = Database::get()->prepare('INSERT INTO Lieu (nom, coordonnees_gps, type_lieu) VALUES (:n, :g, :t)');
        $s->execute([':n' => $d['nom'], ':g' => $d['coordonnees_gps'], ':t' => $d['type_lieu'] ?? 'zone']);
        return (int)Database::get()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $s = Database::get()->prepare('UPDATE Lieu SET nom=:n, coordonnees_gps=:g, type_lieu=:t WHERE id_lieu=:id');
        $s->execute([':n' => $d['nom'], ':g' => $d['coordonnees_gps'], ':t' => $d['type_lieu'] ?? 'zone', ':id' => $id]);
    }

    public static function delete(int $id): void
    {
        $s = Database::get()->prepare('DELETE FROM Lieu WHERE id_lieu=:id');
        $s->execute([':id' => $id]);
    }
}
