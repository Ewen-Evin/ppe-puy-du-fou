<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class JoursModel
{
    public static function all(): array
    {
        return Database::get()->query('SELECT * FROM Jours ORDER BY id_jours')->fetchAll();
    }

    public static function find(string $date): ?array
    {
        $s = Database::get()->prepare('SELECT * FROM Jours WHERE id_jours=:d');
        $s->execute([':d' => $date]);
        return $s->fetch() ?: null;
    }

    public static function upsert(string $date, string $ouverture, string $fermeture): void
    {
        $s = Database::get()->prepare(
            'INSERT INTO Jours (id_jours, heure_ouverture, heure_fermeture)
             VALUES (:d,:o,:f)
             ON DUPLICATE KEY UPDATE heure_ouverture=:o, heure_fermeture=:f'
        );
        $s->execute([':d' => $date, ':o' => $ouverture, ':f' => $fermeture]);
    }

    public static function delete(string $date): void
    {
        $s = Database::get()->prepare('DELETE FROM Jours WHERE id_jours=:d');
        $s->execute([':d' => $date]);
    }
}
