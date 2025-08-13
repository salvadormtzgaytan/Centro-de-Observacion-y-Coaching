<h2 class="text-xl font-semibold text-gray-700 dark:text-gray-100 mt-8 mb-4">
    Funciones como Administrador
</h2>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <x-cards.card
        title="Gestión de Plantillas"
        route="{{ route('admin.templates') }}"
        icon="heroicon-o-rectangle-stack"
        description="Crea y administra las plantillas de guías"
    />

    <x-cards.card
        title="Usuarios y Roles"
        route="{{ route('admin.users') }}"
        icon="heroicon-o-users"
        description="Gestiona cuentas de usuario y sus permisos"
    />
</div>
