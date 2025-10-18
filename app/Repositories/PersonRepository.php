<?php

namespace App\Repositories;

use App\Models\Person;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonRepository {
    public function getAll() {
        return Person::where('is_active', true)->with(['father'])->get();
    }

    public function store(array $data): Person {
        $fatherId = $data['father_id'] ?? null;
        // Verificar si se agregó un padre
        if ($data['father_id'] !== null && !Person::where('id', $fatherId)->exists()) {
            throw new Exception("El padre con ID {$fatherId} no existe.");
        }

        // Crear la persona
        return Person::create($data);
    }

    public function delete(int $idPerson): bool {
        $person = Person::find($idPerson);
        if (!$person) {
            throw new Exception("La persona con ID {$idPerson} no existe.");
        }

        $idsToDelete = $this->getDescendantsIds($idPerson);

        Person::whereIn('id', $idsToDelete)->update(['is_active' => false]);

        return true;
    }

    protected function getDescendantsIds(int $fatherId): array {
        $ids = [];
        $results = DB::select("
            WITH RECURSIVE Descendants AS (
                SELECT id, father_id
                FROM people
                WHERE father_id = ? AND is_active = true
                UNION ALL
                SELECT p.id, p.father_id
                FROM people p
                INNER JOIN Descendants d ON p.father_id = d.id
                WHERE p.is_active = true
            )
            SELECT id FROM Descendants
        ", [$fatherId]);
        $ids[] = $fatherId;
        foreach ($results as $row) {
            $ids[] = $row->id;
        }

        return $ids;
    }

    public function moveDescendants(int $childMoveId, ?int $newFatherId): bool {
        $personToMove = Person::find($childMoveId);
        // Verificar que la persona a mover exista
        if (!$personToMove) {
            Log::error("La persona con ID {$childMoveId} no existe al intentar mover descendientes.");
            throw new Exception("La persona con ID {$childMoveId} no existe.");
        }

        // Si quiere que el hijo se vuelva la raíz (sin padre)
        if ($newFatherId == null) {
            $fatherRoot = Person::whereNull('father_id')
                                ->where('is_active', true)
                                ->where('id', '!=', $childMoveId)
                                ->first();
            if ($fatherRoot) {
                Log::error("Ya existe una persona raíz con ID {$fatherRoot->id}. No se puede tener más de una raíz al intentar mover descendientes.");
                throw new Exception("Ya existe una persona raíz con ID {$fatherRoot->id}. No se puede tener más de una raíz.");
            }
        } else {
            $newFather = Person::find($newFatherId);
            if (!$newFather) {
                Log::error("El nuevo padre con ID {$newFatherId} no existe al intentar mover descendientes.");
                throw new Exception("El nuevo padre con ID {$newFatherId} no existe.");
            }
        }

        // Verificar que no se esté intentando asignar como padre a sí mismo
        if ($childMoveId == $newFatherId) {
            Log::error("No se puede mover la persona con ID {$childMoveId} como su propio padre.");
            throw new Exception("No se puede mover una persona como su propio padre.");
        }

        // Verificar que el nuevo padre no sea el mismo que el actual
        if ($personToMove->father_id == $newFatherId) {
            Log::error("La persona con ID {$childMoveId} ya tiene como padre a la persona con ID {$newFatherId}.");
            throw new Exception("La persona con ID {$childMoveId} ya tiene como padre a la persona con ID {$newFatherId}.");
        }

        if ($newFatherId !== null) {
            // Verificar que no se esté intentando asignar como padre a un descendiente
            $idsChildren = $this->getDescendantsIds($childMoveId);
            if(in_array($newFatherId, $idsChildren)) {
                Log::error("No se puede mover la persona con ID {$childMoveId} como padre de su propio descendiente con ID {$newFatherId}.");
                throw new Exception("No se puede mover a un descendiente como padre.");
            }
        }

        // Actualizar el padre de la persona
        $personToMove->update([
            'father_id' => $newFatherId
        ]);

        return true;
    }

    public function calculatePersonLevel(int $idPerson): int {
        $results = DB::select("
            WITH RECURSIVE Ancestors AS (
                SELECT id, father_id, 1 AS level
                FROM people
                WHERE id = ? AND is_active = true
                UNION ALL
                SELECT p.id, p.father_id, a.level + 1 AS level
                FROM people p
                INNER JOIN Ancestors a ON p.id = a.father_id
                WHERE a.father_id IS NOT NULL AND p.is_active = true
            )
            SELECT MAX(level) as calculated_level 
            FROM Ancestors
        ", [$idPerson]);

        return $results[0]->calculated_level ?? 0;
    }

    public function calculateMaxDepth(): int {
        $results = DB::select("
            WITH RECURSIVE Descendants AS (
                SELECT id, father_id, 1 AS depth
                FROM people
                WHERE father_id IS NULL AND is_active = true
                UNION ALL
                SELECT p.id, p.father_id, d.depth + 1 AS depth
                FROM people p
                INNER JOIN Descendants d ON p.father_id = d.id
                WHERE p.is_active = true
            )
            SELECT MAX(depth) as max_depth 
            FROM Descendants
        ");

        return $results[0]->max_depth ?? 0;
    }

    public function countDescendants(int $idPerson): int {
        $results = DB::select("
            WITH RECURSIVE Descendants AS (
                SELECT id, father_id
                FROM people
                WHERE father_id = ? AND is_active = true
                UNION ALL
                SELECT p.id, p.father_id
                FROM people p
                INNER JOIN Descendants d ON p.father_id = d.id
                WHERE p.is_active = true
            )
            SELECT COUNT(*) as total_descendants 
            FROM Descendants
        ", [$idPerson]);

        return $results[0]->total_descendants ?? 0;
    }

    public function getDFS(int $idPerson): array {
        $sql = "
            WITH RECURSIVE DFS_Ordered AS (
                SELECT 
                    id, 
                    name, 
                    father_id, 
                    CAST(1 AS CHAR(100)) AS path_order,
                    0 AS depth
                FROM people
                WHERE id = ? AND is_active = true

                UNION ALL

                SELECT 
                    p.id, 
                    p.name, 
                    p.father_id,
                    CONCAT(d.path_order, '.', p.id) AS path_order, 
                    d.depth + 1 AS depth
                FROM people p
                INNER JOIN DFS_Ordered d ON p.father_id = d.id
                WHERE p.is_active = true
            )
            SELECT id, name, father_id
            FROM DFS_Ordered
            ORDER BY path_order
        ";

        $results = DB::select($sql, [$idPerson]);

        return collect($results)->map(fn($row) => (array) $row)->all();
    }

    public function getBFS(int $idPerson): array {
        $sql = "
            WITH RECURSIVE BFS AS (
                SELECT id, name, father_id, 1 AS level
                FROM people
                WHERE id = ? AND is_active = true
                UNION ALL
                SELECT p.id, p.name, p.father_id, b.level + 1 AS level
                FROM people p
                INNER JOIN BFS b ON p.father_id = b.id
                WHERE p.is_active = true
            )
            SELECT id, name, father_id, level FROM BFS 
            ORDER BY level, id
        ";

        $results = DB::select($sql, [$idPerson]);

        return collect($results)->map(fn($row) => (array) $row)->all();
    }
}
