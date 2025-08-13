<?php

return [
    // Traducciones generales
    'actions' => [
        'edit' => 'Editar',
        'delete' => 'Eliminar',
        'restore' => 'Restaurar',
        'force_delete' => 'Eliminar permanentemente',
        'delete_selected' => 'Eliminar seleccionados',
        'restore_selected' => 'Restaurar seleccionados',
        'force_delete_selected' => 'Eliminar permanentemente selección',
        'export' => 'Exportar',
        'add_section' => 'Añadir sección',
    ],

    'buttons' => [
        'export_all' => 'Exportar todos',
    ],

    'status' => [
        'draft' => 'Borrador',
        'published' => 'Publicado',
    ],

    'validation' => [
        'at_least_one_section' => 'Debes agregar al menos 1 sección',
    ],

    'misc' => [
        'no_changes' => 'Sin cambios detectados',
        'no_relevant_changes' => 'Cambios no relevantes',
        'empty' => 'N/A',
    ],

    // Traducciones por recurso
    'resources' => [
        'user' => [
            'navigation' => [
                'group' => 'Gestión de Usuarios',
                'label' => 'Usuarios',
                'icon' => 'Usuarios',
                'badge_format' => '{count}',
            ],
            'model' => [
                'singular' => 'Usuario',
                'plural' => 'Usuarios',
            ],
            'fields' => [
                'name' => 'Nombre',
                'email' => 'Correo electrónico',
                'password' => 'Contraseña',
                'parent_id' => 'Supervisor',
                'roles' => 'Roles',
                'deleted_at' => 'Eliminado',
            ],
            'placeholders' => [
                'name' => 'Ingrese el nombre completo del usuario',
                'email' => 'usuario@ejemplo.com',
                'password' => 'Mínimo 8 caracteres',
                'parent' => 'Seleccione un supervisor',
                'roles' => 'Seleccione los roles del usuario',
            ],
            'helper_texts' => [
                'name' => 'Ejemplo: Juan Pérez',
                'password' => 'Debe tener al menos 8 caracteres.',
            ],
            'filters' => [
                'supervisor' => [
                    'label' => 'Supervisor',
                    'placeholder' => 'Cualquiera',
                ],
            ],
            'export' => [
                'label' => 'Exportar Usuarios',
                'file_name' => 'usuarios_export',
                'formats' => ['Xlsx' => 'Excel (.xlsx)'],
            ],
        ],

        // Traducciones para ScaleResource
        'scale' => [
            'fields' => [
                'label' => 'Etiqueta',
                'value' => 'Valor',
                'order' => 'Orden',
                'created_at' => 'Creado el',
                'updated_at' => 'Actualizado el',
            ],
            'placeholders' => [
                'label' => 'Ingrese la etiqueta de la escala',
                'value' => 'Ingrese el valor de la escala',
                'order' => 'Ingrese el orden de la escala',
            ],
            'filters' => [
                'recent' => 'Recientes',
            ],
        ],

        'guide_template' => [
            'fields' => [
                'name' => 'Nombre de la plantilla',
                'division_id' => 'División',
                'level_id' => 'Nivel',
                'channel_id' => 'Canal',
                'status' => 'Estado',
                'sections' => 'Secciones',
                'sections_count' => '# Secciones',
                'section_title' => 'Título de sección',
                'section_order' => 'Orden de sección',
                'created_at' => 'Creado el',
                'updated_at' => 'Actualizado el',
                'deleted_at' => 'Eliminado el',
            ],
            'steps' => [
                'sections_items' => 'Secciones & Ítems',
            ],
        ],

        'template_item' => [
            'fields' => [
                'template_section_id' => 'Sección',
                'question' => 'Pregunta',
                'type' => 'Tipo de respuesta',
                'help_text' => 'Texto de ayuda',
                'options' => 'Opciones',
                'score' => 'Puntaje',
                'order' => 'Orden',
                'created_at' => 'Creado el',
                'updated_at' => 'Actualizado el',
                'deleted_at' => 'Eliminado el',
            ],
            'buttons' => [
                'export_all' => 'Exportar preguntas',
            ],
            'export' => [
                'question' => 'Pregunta',
                'type' => 'Tipo de respuesta',
            ],
        ],

        'activity_log' => [
            'fields' => [
                'log_name' => 'Módulo',
                'description' => 'Descripción',
                'causer' => 'Hecho por',
                'action' => 'Acción',
                'ip' => 'IP de origen',
                'created_at' => 'Fecha',
                'status' => 'Estado',
            ],
        ],

        'channel' => [
            'fields' => [
                'key' => 'Clave',
                'name' => 'Nombre',
                'order' => 'Orden',
                'created_at' => 'Creado el',
                'updated_at' => 'Actualizado el',
            ],
            'buttons' => [
                'export_all' => 'Exportar Canales',
            ],
            'export' => [
                'key' => 'Clave',
                'name' => 'Nombre del canal',
                'order' => 'Orden',
            ],
        ],

        'division' => [
            'fields' => [
                'key' => 'Clave',
                'name' => 'Nombre',
                'order' => 'Orden',
                'created_at' => 'Creado el',
                'updated_at' => 'Actualizado el',
            ],
            'buttons' => [
                'export_all' => 'Exportar Divisiones',
            ],
            'export' => [
                'name' => 'Nombre del canal',
                'order' => 'Orden',
            ],
        ],

        'level' => [
            'fields' => [
                'key' => 'Clave',
                'name' => 'Nombre',
                'order' => 'Orden',
                'created_at' => 'Creado el',
                'updated_at' => 'Actualizado el',
            ],
            'placeholders' => [
                'key' => 'Ingrese la clave del nivel',
                'name' => 'Ingrese el nombre del nivel',
                'order' => 'Ingrese el orden del nivel',
            ],
            'filters' => [
                'recent' => 'Recientes',
            ],
        ],

        'guide_group' => [
            'fields' => [
                'name' => 'Nombre',
                'description' => 'Descripción',
                'active' => 'Activo',
                'created_at' => 'Creado el',
                'updated_at' => 'Actualizado el',
                'deleted_at' => 'Eliminado el',
            ],
            'placeholders' => [
                'name' => 'Ingrese el nombre del grupo',
                'description' => 'Ingrese una descripción del grupo',
            ],
            'filters' => [
                'active' => 'Activos',
            ],
            'relation_managers' => [
                'templates' => [
                    'fields' => [
                        'name' => 'Nombre',
                        'level_id' => 'Nivel',
                        'channel_id' => 'Canal',
                        'status' => 'Estado',
                        'created_at' => 'Creado el',
                        'updated_at' => 'Actualizado el',
                    ],
                    'filters' => [
                        'status' => 'Estado',
                    ],
                ],
            ],
        ],
    ],
];
