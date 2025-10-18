<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        // Desactivar la protección de claves foráneas para limpieza
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Person::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $peopleCount = 50;
        $people = [];
        
        // 1. Crear la Persona Raíz
        $root = Person::create([
            'name' => 'Root (Level 1)',
            'father_id' => null,
            'is_active' => true,
        ]);

        $people[] = $root;
        $lastParentIds = [$root->id]; // IDs de la generación anterior
        $currentLevel = 2;
        $createdCount = 1;

        // 2. Crear las siguientes generaciones (hasta 50 personas)
        while ($createdCount < $peopleCount && !empty($lastParentIds)) {
            $nextParentIds = [];
            $parentsToProcess = count($lastParentIds);

            // Intentamos que cada padre tenga entre 1 y 3 hijos
            foreach ($lastParentIds as $fatherId) {
                // Si ya alcanzamos el límite, salimos
                if ($createdCount >= $peopleCount) {
                    break 2; // Salir de ambos bucles
                }

                $numChildren = rand(1, 3); 
                
                for ($i = 0; $i < $numChildren; $i++) {
                    if ($createdCount < $peopleCount) {
                        $person = Person::create([
                            'name' => "Persona {$createdCount} (Nivel {$currentLevel})",
                            'father_id' => $fatherId,
                            'is_active' => true,
                        ]);

                        $people[] = $person;
                        $nextParentIds[] = $person->id;
                        $createdCount++;
                    }
                }
            }

            $lastParentIds = $nextParentIds;
            $currentLevel++;
        }
        
        $this->command->info("Se creó un árbol de {$createdCount} personas hasta el nivel " . ($currentLevel - 1) . ".");
    }
}
