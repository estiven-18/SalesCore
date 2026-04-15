## 1) Instalar

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## 2) Crear usuario para entrar al panel Dashboard

```bash
php artisan make:filament-user
```

Completa nombre, email y password para poder entrar al admin .

## 3) Crear modelo nuevo

```bash
php artisan make:model NombreModelo
```


## 4) Crear CRUD (Resource) en Filament

```bash
php artisan make:filament-resource NombreModelo
```

Cuando salgan estas preguntas, usa este flujo:

- What is the title attribute for this model? -> name
- Would you like to generate a read-only view page for the resource? -> yes
- Should the configuration be generated from the current database columns? -> yes
- Does the model use soft-deletes? -> yes

## 5) Cambios obligatorios despues de generar



2. Ajustar el modelo:
    - Archivo en app/Models/NombreModelo.php
    - Completar protected $fillable con los campos editables
    - Definir relaciones (belongsTo, hasMany, belongsToMany) si aplica

3. Ajustar formulario del CRUD:
    - Archivo en app/Filament/Resources/NombreModelos/Schemas/NombreModeloForm.php
    - Cambiar los inputs para que coincidan con los campos reales de la tabla
    - Ejemplo: TextInput para texto, Toggle para boolean, Select para relaciones

4. Ajustar vista de detalle (View):
    - Archivo en app/Filament/Resources/NombreModelos/Schemas/NombreModeloInfolist.php
    - Agregar los campos que quieres mostrar al abrir "View"

5. Ajustar tabla de listado:

     (esto lo hace si no salen los campos del la tabla)
    - Archivo en app/Filament/Resources/NombreModelos/Tables/NombreModelosTable.php
    - Mostrar columnas utiles, labels y formatos
    
## 6) Crear una rama facil y subirla

Usa este flujo cuando termines una tarea:

```bash
git checkout -b nombreDelaRama
git add .
git commit -m "descripcion corta del cambio"
git push -u origin nombreDelaRama
```

Despues, en GitHub crea el Pull Request de esa rama hacia main.

