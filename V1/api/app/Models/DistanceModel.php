<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class DistanceModel
{
    public static function all(): array
    {
        return Database::get()->query('SELECT * FROM Distance')->fetchAll();
    }

    public static function create(int $a, int $b, int $metres): void
    {
        $s = Database::get()->prepare(
            'INSERT INTO Distance (id_lieu, id_lieu_1, distance_metres) VALUES (:a,:b,:m)'
        );
        $s->execute([':a' => $a, ':b' => $b, ':m' => $metres]);
    }

    public static function delete(int $a, int $b): void
    {
        $s = Database::get()->prepare('DELETE FROM Distance WHERE id_lieu=:a AND id_lieu_1=:b');
        $s->execute([':a' => $a, ':b' => $b]);
    }
}
