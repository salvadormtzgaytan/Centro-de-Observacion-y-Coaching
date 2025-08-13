<?php

declare(strict_types=1);

return [
    'settings'                => 'Ajustes',
    'manage_account_settings' => 'Administra los ajustes de tu perfil y cuenta',
    'dashboard'               => 'Escritorio',
    'repository'              => 'Repositorio',
    'documentation'           => 'Documentación',
    'admin_panel'             => 'Panel Administrativo',
    'guide_templates'         => 'Crea tu evaluación',
    'system_logs'             => 'Logs del Sistema',
    'new_guide'               => 'Nueva Guía',
    'in_progress_guides'      => 'Guías en Curso',
    'guide_history'           => 'Historial de Guías',
    'pending_signatures'      => 'Firmas Pendientes',
    'signatures'              => 'Firmado',
    'signed'                  => 'Firmada',
    'started'                 => 'Fecha de inicio',
    'ended'                   => 'Fecha de termino',
    'updated'                 => 'Fecha de actualización',
    'progress'                => 'Progreso',
    'no_active_sessions'      => 'No tienes sesiones activas.',
    'my_evaluations'          => 'Mis Evaluaciones',
    'logout'                  => 'Cerrar sesión',
    'platform'                => 'Plataforma',
    'repository'              => 'Repositorio',
    'documentation'           => 'Documentación',
    'coach_functions'         => 'Funciones como Coach',
    'guides'                  => 'Guías',
    'new_evaluation'          => 'Nueva Evaluación',
    'in_progress_evaluation'  => 'Evaluaciones en Curso',
    'evaluation_history'      => 'Historial de Evaluaciones',

    // Descripciones opcionales
    'desc_admin_panel'        => 'Accede al backend completo del sistema',
    'desc_guide_templates'    => 'Consulta y gestiona plantillas de guías',
    'desc_system_logs'        => 'Auditoría de acciones realizadas por usuarios',
    'desc_new_guide'          => 'Inicia una nueva guía',
    'desc_in_progress_guides' => 'Edita guías que aún están en progreso',
    'desc_guide_history'      => 'Consulta historial de guías',
    'desc_pending_signatures' => 'Guías que requieren tu firma',
    'desc_my_evaluations'     => 'Historial de observaciones o coaching recibidas',
    'desc_logout'             => 'Cerrar sesión',
    'edit'                    => 'Editar',
    'delete'                  => 'Eliminar',
    'show'                    => 'Continuar',
    'create'                  => 'Crear',
    'guide'                   => [
        'edit'   => 'Editar Guía',
        'delete' => 'Eliminar Guía',
    ],
    'evaluation' => [
        'desc_menu' => 'Evaluaciones',
        'edit'      => 'Editar Evaluación',
        'delete'    => 'Eliminar Evaluación'
    ],
    'coachee' => [
        'desc_menu'            => 'Mis Evaluaciones',
        'edit'                 => 'Editar Evaluación',
        'delete'               => 'Eliminar Evaluación',
        'select_coachee'       => 'Selecciona a tu Evaluado',
        'no_coachees_assigned' => 'Sin datos disponibles'
    ],

    'select_templates'   => 'Selecciona las plantillas',
    'date'               => 'Fecha de sesión',
    'cycle'              => 'Ciclo',
    'division'           => 'Línea Terapéutica',
    'select_division'    => 'Selecciona una Línea Terapéutica',
    'cancel'             => 'Cancelar',
    'templates_count'    => ':count guías en este grupo',
    'guide_groups'       => 'Evaluaciones dispobibles',
    'no_groups' => 'Sin Evaluaciones dispobibles',
    'no_groups_or_templates' => 'No existen guías ni evaluaciones disponibles. Por favor, contacta al administrador.',
    // Puedes agregar más si lo deseas:
    'no_templates'      => 'No hay plantillas disponibles',
    'next' => 'Siguiente',
    'back' => 'Regresar',
    'confirm_delete_title' => '¿Eliminar sesión?',
    'confirm_delete_body'  => 'Esta acción no se puede deshacer y se perderá el progreso.',
    'confirm_delete_ok'    => 'Sí, eliminar',

    // Opcionales (para la tabla/filtros de historial si quieres evitar textos hardcodeados)
    'table' => [
        'id'       => 'ID',
        'date'     => 'Fecha',
        'coachee'  => 'Coachee',
        'division' => 'División',
        'cycle'    => 'Ciclo',
        'score'    => 'Puntaje',
        'status'   => 'Estado',
        'actions'  => 'Acciones',
        'no_rows'  => 'No se encontraron sesiones.',
    ],

    'filters_ui' => [
        'coachee'       => 'Coachee',
        'division'      => 'División',
        'cycle'         => 'Ciclo',
        'date_from_to'  => 'Fecha desde / hasta',
        'filter'        => 'Filtrar',
        'clear'         => 'Limpiar',
        'all'           => 'Todos',
        'all_divisions' => 'Todas',
        'all_cycles'    => 'Todos',
        'export_excel'  => 'Exportar a Excel',
    ],

    // Opcional: catálogo de estados por si te sirve en otras vistas
    'statuses' => [
        'draft'       => 'Borrador',
        'in_progress' => 'En progreso',
        'completed'   => 'Completada',
        'signed'      => 'Firmada',
    ],

];
