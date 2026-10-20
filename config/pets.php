<?php

/*
|--------------------------------------------------------------------------
| Catálogos de PetRescue (Sprint 2 — HU-13)
|--------------------------------------------------------------------------
| Listas cerradas que usan los formularios de reporte. Solo se aceptan
| animales domésticos que comúnmente se extravían, para evitar reportes
| que no correspondan (ej. "perdí una mesa"). Todo lo que el usuario
| elige sale de aquí y el backend valida contra estas mismas llaves.
*/

return [

    'species' => [
        'perro' => [
            'label' => 'Perro',
            'emoji' => '🐶',
            'breeds' => [
                'Criollo / mestizo', 'Labrador', 'Golden retriever', 'Pastor alemán',
                'French poodle', 'Schnauzer', 'Pinscher', 'Shih tzu', 'Yorkshire',
                'Beagle', 'Bulldog francés', 'Pitbull', 'Husky', 'Chihuahua',
                'Border collie', 'Pug', 'Otra raza',
            ],
            'uses_size' => true,
        ],
        'gato' => [
            'label' => 'Gato',
            'emoji' => '🐱',
            'breeds' => [
                'Criollo / mestizo', 'Siamés', 'Persa', 'Angora', 'Bengalí',
                'Maine coon', 'Esfinge', 'Otra raza',
            ],
            'uses_size' => false,
        ],
        'conejo' => [
            'label' => 'Conejo',
            'emoji' => '🐰',
            'breeds' => ['Mestizo', 'Belier (orejas caídas)', 'Cabeza de león', 'Enano holandés', 'Otra raza'],
            'uses_size' => false,
        ],
        'ave' => [
            'label' => 'Ave',
            'emoji' => '🐦',
            'breeds' => ['Periquito australiano', 'Canario', 'Loro', 'Cacatúa / ninfa', 'Agapornis', 'Otra'],
            'uses_size' => false,
        ],
        'roedor' => [
            'label' => 'Hámster o cobayo',
            'emoji' => '🐹',
            'breeds' => ['Hámster', 'Cobayo', 'Chinchilla', 'Otro'],
            'uses_size' => false,
        ],
    ],

    // Opción extra en "raza" para quien encuentra una mascota y no la reconoce.
    'unknown_breed' => 'No sé / no estoy seguro',

    'colors' => [
        'negro' => ['label' => 'Negro', 'hex' => '#1f2937'],
        'blanco' => ['label' => 'Blanco', 'hex' => '#ffffff'],
        'cafe' => ['label' => 'Café', 'hex' => '#7c4a1e'],
        'gris' => ['label' => 'Gris', 'hex' => '#9ca3af'],
        'crema' => ['label' => 'Crema / dorado', 'hex' => '#f3d9a4'],
        'naranja' => ['label' => 'Naranja', 'hex' => '#f59e0b'],
        'atigrado' => ['label' => 'Atigrado', 'hex' => 'repeating-linear-gradient(45deg,#7c4a1e 0 4px,#d6a85f 4px 8px)'],
        'manchado' => ['label' => 'Manchado', 'hex' => 'radial-gradient(circle at 30% 30%,#1f2937 0 25%,#fff 26%)'],
        'verde' => ['label' => 'Verde', 'hex' => '#22c55e'],
        'amarillo' => ['label' => 'Amarillo', 'hex' => '#facc15'],
        'azul' => ['label' => 'Azul', 'hex' => '#3b82f6'],
    ],

    'max_colors' => 3,

    'sizes' => [
        'pequeño' => 'Pequeño (hasta 10 kg)',
        'mediano' => 'Mediano (10 a 25 kg)',
        'grande' => 'Grande (más de 25 kg)',
    ],

    'sexes' => [
        'macho' => 'Macho',
        'hembra' => 'Hembra',
        'no_se' => 'No sé',
    ],

    // Un reporte no puede referirse a un evento de hace más de estos días.
    'max_days_ago' => 90,

    // Límites aproximados de Colombia: el punto del mapa debe caer adentro.
    'bounds' => [
        'lat_min' => -4.3, 'lat_max' => 13.5,
        'lng_min' => -82.0, 'lng_max' => -66.8,
    ],

    // Centro por defecto de los mapas: Pasto, Nariño.
    'default_center' => ['lat' => 1.2136, 'lng' => -77.2811, 'zoom' => 13],

];
