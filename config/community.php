<?php

/*
|--------------------------------------------------------------------------
| Secciones de la comunidad (Sprint 2 — HU-15 / HU-16)
|--------------------------------------------------------------------------
| 'directory' = categorías del directorio de servicios con mapa (HU-16).
| 'topics'    = espacios de conversación. Se muestran desde la página
|               principal y se habilitan para publicar en el Sprint 3 (HU-17).
*/

return [

    'directory' => [
        'tienda' => [
            'label' => 'Tiendas de alimento y accesorios',
            'short' => 'Tiendas',
            'emoji' => '🛒',
            'color' => '#f97316',
            'blurb' => 'Concentrado, snacks, camas, collares y juguetes.',
        ],
        'guarderia' => [
            'label' => 'Guarderías y hoteles',
            'short' => 'Guarderías',
            'emoji' => '🏡',
            'color' => '#8b5cf6',
            'blurb' => 'Cuidado por días, hospedaje y paseos.',
        ],
        'peluqueria' => [
            'label' => 'Baño y peluquería',
            'short' => 'Baño y peluquería',
            'emoji' => '🛁',
            'color' => '#06b6d4',
            'blurb' => 'Baño, corte, uñas y limpieza de oídos.',
        ],
        'veterinaria' => [
            'label' => 'Veterinarias',
            'short' => 'Veterinarias',
            'emoji' => '🩺',
            'color' => '#10b981',
            'blurb' => 'Consulta, vacunas, cirugía y urgencias.',
        ],
        'drogueria' => [
            'label' => 'Droguerías veterinarias',
            'short' => 'Droguerías',
            'emoji' => '💊',
            'color' => '#ef4444',
            'blurb' => 'Medicamentos, desparasitantes y antipulgas.',
        ],
    ],

    'topics' => [
        'rescatadas' => [
            'label' => 'Mascotas rescatadas',
            'emoji' => '🤲',
            'blurb' => 'Cómo adaptar a un animal rescatado: primeros días, miedos, confianza y rutina.',
        ],
        'reactivas' => [
            'label' => 'Mascotas reactivas',
            'emoji' => '⚡',
            'blurb' => 'Ladridos, tirones o miedo a otros perros y personas. Experiencias y manejo.',
        ],
        'entrenamiento' => [
            'label' => 'Educación y entrenamiento',
            'emoji' => '🎾',
            'blurb' => 'Paseo con correa, hacer sus necesidades en el lugar correcto, trucos básicos.',
        ],
        'salud' => [
            'label' => 'Salud y cuidados',
            'emoji' => '❤️',
            'blurb' => 'Vacunas, esterilización, desparasitación y señales de alerta.',
        ],
        'alimentacion' => [
            'label' => 'Alimentación',
            'emoji' => '🥣',
            'blurb' => 'Qué, cuánto y cuándo darles de comer. Alimentos prohibidos.',
        ],
        'adopcion' => [
            'label' => 'Adopción responsable',
            'emoji' => '🏠',
            'blurb' => 'Fundaciones, jornadas de adopción y qué tener en cuenta antes de adoptar.',
        ],
    ],

];
