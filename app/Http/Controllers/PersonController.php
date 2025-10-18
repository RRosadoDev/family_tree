<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoveDescendantsRequest;
use App\Http\Requests\PersonRequest;
use App\Services\PersonService;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PersonController extends Controller {
    public function __construct(protected PersonService $personService) {}

    public function index() {
        try {
            $people = $this->personService->getAll();

            // 1. Construir la estructura jerárquica
            $children = $this->buildTreantStructure($people, null);
            
            // 2. Crear el nodo RAÍZ del árbol para Treant.js
            // Si tienes una única raíz, la pones como el primer elemento.
            if (!empty($children)) {
                // Asumimos que el primer elemento del $children es la raíz principal
                $rootNode = $children[0];
            } else {
                // En caso de que no haya datos.
                $rootNode = ['text' => ['name' => 'Árbol Vacío']];
            }
            
            // Treant.js espera que la estructura de nodo esté contenida en sí misma.
            // Si tu árbol tiene una única raíz, pasa esa raíz directamente.
            $treeData = $rootNode;

            return view('people.index', compact('treeData', 'people'));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function buildTreantStructure(Collection $people, $parentId = null): array {
        $branch = [];
        
        // Filtra las personas que son hijos del padre actual
        foreach ($people->where('father_id', $parentId) as $person) {
            
            // El formato del nodo Treant.js es un array asociativo con propiedades.
            $node = [
                // HTML para el contenido del nodo (puedes usar Blade)
                'text' => [
                    'name' => $person->name,
                    // Puedes añadir más información aquí
                    'title' => "ID: {$person->id}", 
                ],
                // Los hijos deben estar dentro del índice 'children'
                'children' => $this->buildTreantStructure($people, $person->id)
            ];
            
            $branch[] = $node;
        }
        
        return $branch;
    }

    public function store(PersonRequest $request) {
        try {
            $validatedData = $request->all();
            $this->personService->store($validatedData);
            return to_route('people.index')->with('success', 'Persona creada exitosamente.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function delete(int $id) {
        try {
            $this->personService->delete($id);
            return to_route('people.index')->with('success', 'Persona eliminada exitosamente.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function moveDescendants(MoveDescendantsRequest $request) {
        $childMoveId = $request->input('child_move_id');
        $newFatherId = $request->input('new_father_id');

        try {
            $this->personService->moveDescendants($childMoveId, $newFatherId);
            return to_route('people.index')->with('success', 'Movido exitosamente.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function getLevelPerson(int $idPerson) {
        try {
            $level = $this->personService->getLevelPerson($idPerson);
            return to_route('people.index')->with('success', "La person con ID {$idPerson} está en el nivel {$level}.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function getMaxDepth() {
        try {
            $maxLevel = $this->personService->getMaxDepth();

            return to_route('people.index')->with('success', "La profundidad máxima del árbol es {$maxLevel}.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function getAmountDescendants(int $idPerson) {
        try {
            $descendants = $this->personService->getAmountDescendants($idPerson);
            return to_route('people.index')->with('success', "La persona con ID {$idPerson} tiene {$descendants} descendientes.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function getDFS(int $idPerson) {
        try {
            $dfsResult = $this->personService->getDFS($idPerson);
            $dfsCollection = collect($dfsResult);
            $dfsIds = $dfsCollection->pluck('name')->toArray();
            $resultString = implode(', ', $dfsIds);

            return to_route('people.index')->with('success', "Recorrido DFS desde la persona con ID {$idPerson}: " . $resultString);
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function getBFS(int $idPerson) {
        try {
            $bfsResult = $this->personService->getBFS($idPerson);
            $bfsIds = collect($bfsResult)->pluck('name')->toArray();
            $resultString = implode(', ', $bfsIds);

            return to_route('people.index')->with('success', "Recorrido BFS desde la persona con ID {$idPerson}: " . $resultString);
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
