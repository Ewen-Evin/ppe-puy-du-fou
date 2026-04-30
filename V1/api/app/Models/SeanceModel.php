<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SeanceModel
{
    public static function all(): array
    {
        $s = Database::get()->query(
            'SELECT se.*, sp.libelle, sp.id_lieu, sp.duree_attente
             FROM Seance se JOIN Spectacle sp ON sp.id_spectacle = se.id_spectacle
             ORDER BY se.date_seance, se.heure_debut'
        );
        return $s->fetchAll();
    }

    public static function byDate(string $date): array
    {
        $s = Database::get()->prepare(
            'SELECT se.*, sp.libelle, sp.id_lieu, sp.duree_attente
             FROM Seance se JOIN Spectacle sp ON sp.id_spectacle = se.id_spectacle
             WHERE se.date_seance = :d
             ORDER BY se.heure_debut'
        );
        $s->execute([':d' => $date]);
        return $s->fetchAll();
    }

    public static function bySpectacle(int $idSpectacle): array
    {
        $s = Database::get()->prepare(
            'SELECT se.*, sp.libelle, sp.id_lieu, sp.duree_attente
             FROM Seance se JOIN Spectacle sp ON sp.id_spectacle = se.id_spectacle
             WHERE se.id_spectacle = :id
             ORDER BY se.date_seance, se.heure_debut'
        );
        $s->execute([':id' => $idSpectacle]);
        return $s->fetchAll();
    }

    public static function bySpectacleAndDate(int $idSpectacle, string $date): array
    {
        $s = Database::get()->prepare(
            'SELECT se.*, sp.id_lieu, sp.duree_attente, sp.libelle
             FROM Seance se JOIN Spectacle sp ON sp.id_spectacle = se.id_spectacle
             WHERE se.id_spectacle = :id AND se.date_seance = :d
             ORDER BY se.heure_debut'
        );
        $s->execute([':id' => $idSpectacle, ':d' => $date]);
        return $s->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $s = Database::get()->prepare('SELECT * FROM Seance WHERE id_seance=:id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public static function create(array $d): int
    {
        $s = Database::get()->prepare(
            'INSERT INTO Seance (date_seance, heure_debut, heure_fin, id_spectacle)
             VALUES (:date, :hd, :hf, :id_sp)'
        );
        $s->execute([
            ':date' => $d['date_seance'], ':hd' => $d['heure_debut'],
            ':hf' => $d['heure_fin'], ':id_sp' => (int)$d['id_spectacle'],
        ]);
        return (int)Database::get()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        $s = Database::get()->prepare(
            'UPDATE Seance SET date_seance=:date, heure_debut=:hd, heure_fin=:hf, id_spectacle=:id_sp
             WHERE id_seance=:id'
        );
        $s->execute([
            ':date' => $d['date_seance'], ':hd' => $d['heure_debut'],
            ':hf' => $d['heure_fin'], ':id_sp' => (int)$d['id_spectacle'], ':id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $s = Database::get()->prepare('DELETE FROM Seance WHERE id_seance=:id');
        $s->execute([':id' => $id]);
    }
}
