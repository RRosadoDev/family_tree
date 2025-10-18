# family_tree

Sistema para gestionar un árbol genealógico dinámico (Laravel + Treant.js).

## Características
- CRUD básico de personas.
- Visualización del árbol con Treant.js.
- Movimientos de subárboles (mover descendientes).
- Endpoints para obtener nivel, profundidad, DFS y BFS.
- Validación centralizada con FormRequest.

## Requisitos (Windows / Laragon)
- PHP >= 8.x
- Composer
- MySQL (incluido en Laragon)
- Node.js + npm (si quieres compilar assets)
- Laragon (opcional) para entorno local

## Instalación (rápida)

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/RRosadoDev/family_tree.git
    cd family_tree
    ```

1. Instalar dependencias PHP
   ```
   composer install
   ```

2. Copiar .env y generar APP_KEY
   ```
   cp .env.example .env
   php artisan key:generate
   ```

3. Configurar la base de datos en `.env`
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=family_tree
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Migrar y (opcional) sembrar datos
   ```
   php artisan migrate
   php artisan db:seed --class=DatabaseSeeder
   ```

5. Instalar y compilar assets (opcional)
   ```
   npm install
   npm run dev   # o npm run build
   ```

6. Ejecutar la aplicación
   - Con Laragon: iniciar Apache/MySQL y abrir `http://localhost/family_tree/public` (según tu setup).
   - Con artisan:
   ```
   php artisan serve --host=127.0.0.1 --port=8000
   ```

## Rutas principales
(Revisa `routes/web.php` para rutas exactas)
- GET  /people          -> lista / vista principal
- POST /people          -> store (crear persona)
- DELETE /people/{id}   -> eliminar persona
- POST /people/move     -> mover descendientes
- GET /people/{id}/level, /max-depth, /{id}/dfs, /{id}/bfs

## Estructura relevante
- app/Http/Controllers/PersonController.php
- app/Http/Requests/PersonRequest.php
- app/Services/PersonService.php
- resources/views/people/index.blade.php
- public assets: Treant.js + Bootstrap
