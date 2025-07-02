# Centro de Observación y Coaching

**El sistema de gestión de observación y coaching para facilitar guías, evidencias, firmas y reportes.**

---

## Tecnologías principales

* PHP 8.2 · Laravel 12
* Livewire (Flux + Volt)
* Filament 3 · Filament Shield
* MariaDB
* Tailwind CSS · Vite
* Alpine.js
* Spatie Permission · Shield · MediaLibrary · Activitylog
* DomPDF · Laravel Excel
* Socialite · Azure AD

---

## Prerrequisitos

* PHP ≥8.2
* Composer
* Node.js & npm
* MariaDB (o MySQL)

---

## Clonar y levantar el proyecto

1. **Clonar** el repositorio:

   ```bash
   git clone <tu-repo-url> centro-coaching
   cd centro-coaching
   ```

2. **Instalar dependencias PHP**:

   ```bash
   composer install
   ```

3. **Instalar dependencias JS/CSS**:

   ```bash
   npm install
   ```

4. **Copiar `.env` y generar clave**:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar variables de entorno** en `.env`:

   * Base de datos (DB\_CONNECTION, DB\_HOST, DB\_DATABASE, DB\_USERNAME, DB\_PASSWORD)
   * Azure AD (AZURE\_CLIENT\_ID, AZURE\_CLIENT\_SECRET, AZURE\_REDIRECT\_URI)
   * Mail, Redis, etc.

6. **Ejecutar migraciones y seeders**:

   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Publicar y actualizar traducciones** (opcional/periódico):

   ```bash
   php artisan lang:add es
   php artisan lang:update
   ```

8. **Limpiar cachés**:

   ```bash
   php artisan optimize:clear
   ```

9. **Levantar servidor y watcher**:

   ```bash
   npm run dev         # Vite en modo watch
   php artisan serve   # http://localhost:8000
   ```

10. **Panel de administración**:

    * Accede a `/admin` para Filament + Shield

---

## Comandos útiles

* `composer dump-autoload` — regenerar autoload
* `php artisan optimize:clear` — limpiar todas las cachés
* `php artisan migrate` — ejecutar migraciones
* `php artisan db:seed` — ejecutar seeders
* `npm run build` — compilar assets para producción

---

## Estructura clave

```
├── app/                # Lógica de negocio, modelos, Livewire
├── database/           # Migrations, seeders, factories
├── resources/          # Vistas Blade, lang, assets
│   ├── lang/es         # Traducciones en español 🇲🇽
│   └── views/components/layouts
└── vite.config.js      # Configuración de Vite y plugins
```

---

## Contribuir

1. Crear un branch: `git checkout -b feature/mi-nueva-funcionalidad`
2. Hacer cambios y commitear: `git commit -m "feat: descripción"`
3. Push y abrir PR

---

© 2025 Centro de Observación y Coaching
