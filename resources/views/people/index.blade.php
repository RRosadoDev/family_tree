<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Árbol Genealógico</title>

    {{-- Incluye las librerías necesarias (puedes descargarlas o usar CDN) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/treant-js/Treant.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" 
        rel="stylesheet" 
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" 
        crossorigin="anonymous"
    >
    
    {{-- Estilos mínimos (Se recomienda usar tu propio CSS) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/treant-js/Treant.css">
    <style>
        /* Estilos básicos para el contenedor del árbol */
        #chart {
            text-align: center;
            height: 100vh;
        }
        /* Estilo para las tarjetas de persona */
        .node {
            padding: 5px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-4">
                <form id="addPersonForm" class="mb-4" method="POST" action="{{ route('people.store') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Agregar persona
                            </h4>
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control" value="{{ old('name') }}" name="name" placeholder="Nombre">
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="father_id">
                                <option value="">Seleccionar padre</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}" @selected(old('father_id'))>{{ $person->name }}</option>
                                @endforeach
                            </select>
                            @error('father_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success" form="addPersonForm" type="submit">
                                Agregar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-4">
                <form id="deletePersonForm" class="mb-4" method="POST" action="{{ route('people.delete', ['id' => 0]) }}">
                    @method('delete')
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Eliminar persona
                            </h4>
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="id" id="personToDeleteId">
                                <option value="">Seleccionar persona</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                            @error('id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <button class="btn btn-danger" type="submit">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-4">
                <form id="addPersonForm" class="mb-4" method="POST" action="{{ route('people.moveDescendants') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Mover a otro padre
                            </h4>
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="child_move_id">
                                <option value="">Seleccionar hijo a mover</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                            @error('child_move_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="new_father_id">
                                <option value="">Seleccionar nuevo padre</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                            @error('new_father_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">
                                Mover
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <form id="getLevelPersonForm" class="mb-4" method="GET" action="{{ route('people.getLevelPerson', ['id' => 0]) }}">
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Obtener nivel de persona
                            </h4>
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="id" id="personToGetLevelId">
                                <option value="">Seleccionar persona</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-warning" type="submit">
                                Buscar nivel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-4">
                <form id="getMaxDepthForm" class="mb-4" method="GET" action="{{ route('people.getMaxDepth') }}">
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Obtener máxima profundidad
                            </h4>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-warning" type="submit">
                                Obtener
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-4">
                <form id="getAmountDescendantsForm" class="mb-4" method="GET" action="{{ route('people.getAmountDescendants', ['id' => 0]) }}">
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Calcular cantidad descendientes
                            </h4>
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="id" id="personToGetAmountDescendantId">
                                <option value="">Seleccionar persona</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-warning" type="submit">
                                Calcular
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <form id="getDFSForm" class="mb-4" method="GET" action="{{ route('people.getDFS', ['id' => 0]) }}">
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Recorrido DFS
                            </h4>
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="id" id="personToGetDFS">
                                <option value="">Seleccionar persona</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-info" type="submit">
                                Obtener DFS
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-6">
                <form id="getBFSForm" class="mb-4" method="GET" action="{{ route('people.getBFS', ['id' => 0]) }}">
                    <div class="row g-2">
                        <div class="col-12">
                            <h4>
                                Recorrido BFS
                            </h4>
                        </div>
                        <div class="col-12">
                            <select class="form-select" name="id" id="personToGetBFS">
                                <option value="">Seleccionar persona</option>
                                @foreach($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-info" type="submit">
                                Obtener BFS
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="chart"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" 
        crossorigin="anonymous"
    ></script>
    <script>
        // 1. OBTENER LOS DATOS DEL BACKEND (Laravel)
        // Usamos Blade para inyectar la data procesada directamente en JavaScript.
        const treeStructure = @json($treeData);

        // 2. CONFIGURACIÓN INICIAL DE TREANT.JS
        // Define cómo se dibujará el árbol.
        const treeConfig = {
            chart: {
                container: "#chart", // ID del elemento donde se renderizará
                rootOrientation: "NORTH", // 'NORTH', 'EAST', 'SOUTH', 'WEST'
                levelSeparation: 30, // Separación entre niveles (profundidad)
                siblingSeparation: 20, // Separación entre hermanos
                subTeeSeparation: 20, // Separación entre subárboles grandes
                padding: 5,
                nodeAlign: "CENTER",
                
                // Las opciones de animación mejoran la experiencia
                animation: {
                    nodeAnimation: "easeOutExpo",
                    nodeSpeed: 700,
                    connectorsAnimation: "easeOutExpo",
                    connectorsSpeed: 700
                }
            },
            nodeStructure: treeStructure // Aquí se insertan los nodos
        };

        // 3. RENDERIZADO
        // La clase Treant se encarga de todo.
        new Treant(treeConfig);

        document.addEventListener('DOMContentLoaded', function () {
            // Función genérica para manejar la reescritura de la URL antes del envío
            function setupUrlRewriter(formId, selectId, confirmationMessage) {
                const form = document.getElementById(formId);
                const select = document.getElementById(selectId);
                
                // Si no encontramos el formulario o el select, salimos de la función
                if (!form || !select) {
                    console.warn(`No se encontró el formulario (${formId}) o el select (${selectId}).`);
                    return;
                }

                // Obtener la URL base asumiendo que el placeholder es '0' al final.
                // Ejemplo: '/people/0' -> '/people/'
                const baseUrl = form.getAttribute('action').replace(/0$/, ''); 

                form.addEventListener('submit', function (e) {
                    const selectedId = select.value;
                    
                    if (!selectedId) {
                        e.preventDefault();
                        alert("Por favor, selecciona una persona.");
                        return;
                    }

                    // Aplicar confirmación solo si se proporciona un mensaje (útil para DELETE)
                    if (confirmationMessage && !confirm(confirmationMessage)) {
                        e.preventDefault();
                        return;
                    }

                    // Reescribir el ACTION del formulario con el ID seleccionado
                    form.action = baseUrl + selectedId;
                });
            }

            // --- Configuración de Formularios ---

            // ELIMINAR
            setupUrlRewriter(
                'deletePersonForm', 
                'personToDeleteId', 
                "ADVERTENCIA: ¿Estás seguro de que deseas eliminar esta persona y TODO su subárbol?"
            );

            // OBTENER NIVEL
            setupUrlRewriter(
                'getLevelPersonForm', 
                'personToGetLevelId',
                null // No requiere confirmación
            );

            // OBTENER CANTIDAD DESCENDIENTES
            setupUrlRewriter(
                'getAmountDescendantsForm', 
                'personToGetAmountDescendantId',
                null // No requiere confirmación
            );

            // OBTENER DFS
            setupUrlRewriter(
                'getDFSForm', 
                'personToGetDFS',
                null // No requiere confirmación
            );

            // OBTENER BFS
            setupUrlRewriter(
                'getBFSForm', 
                'personToGetBFS',
                null // No requiere confirmación
            );
        });
    </script>
</body>
</html>