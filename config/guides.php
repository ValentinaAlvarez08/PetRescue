<?php

/*
|--------------------------------------------------------------------------
| Guías rápidas (Sprint 4 — HU-21)
|--------------------------------------------------------------------------
| Contenido corto y práctico, agrupado por tema. Cada guía tiene pasos
| numerados y, si aplica, una alerta y enlaces a la comunidad o al
| directorio. Las de salud recomiendan siempre consultar al veterinario:
| no reemplazan una consulta.
|
| 'community' = llave de config('community.topics') relacionada.
| 'directory' = llave de config('community.directory') relacionada.
*/

return [

    'topics' => [
        'perdidas' => ['label' => 'Mascotas perdidas', 'emoji' => '🔎', 'color' => '#f43f5e'],
        'emergencias' => ['label' => 'Emergencias', 'emoji' => '🚑', 'color' => '#ef4444'],
        'salud' => ['label' => 'Salud y cuidados', 'emoji' => '❤️', 'color' => '#10b981'],
        'alimentacion' => ['label' => 'Alimentación', 'emoji' => '🥣', 'color' => '#f97316'],
        'convivencia' => ['label' => 'Convivencia', 'emoji' => '🏡', 'color' => '#8b5cf6'],
    ],

    'guides' => [

        'perdi-mi-mascota' => [
            'topic' => 'perdidas',
            'emoji' => '😿',
            'title' => 'Se perdió mi mascota: qué hacer en las primeras 24 horas',
            'summary' => 'Las primeras horas son las más importantes. Sigue estos pasos en orden.',
            'minutes' => 3,
            'steps' => [
                'Busca primero muy cerca: la mayoría se esconde a pocas cuadras (debajo de carros, en jardines, garajes o construcciones). Ve llamándola con calma y llevando su comida o un juguete que suene.',
                'Publica el reporte en PetRescue con una foto reciente y marca en el mapa dónde la viste por última vez. Así les llega un aviso a los vecinos con alertas activas.',
                'Comparte el reporte por WhatsApp y Facebook (grupos del barrio, de mascotas de Pasto y de tu conjunto). Usa el botón Compartir del reporte.',
                'Deja una prenda con tu olor o su cama en la puerta de la casa: muchas mascotas vuelven siguiendo el olor.',
                'Llama o visita las veterinarias y guarderías cercanas: alguien pudo haberla llevado ahí. Encuéntralas en el directorio.',
                'Revisa varias veces al día los reportes de mascotas encontradas: PetRescue te muestra las que se parecen a la tuya en tu enlace privado.',
                'Pega carteles con foto, nombre y tu celular en tiendas, esquinas y paraderos de la zona.',
            ],
            'warning' => 'Desconfía de quien te pida dinero por adelantado para devolverte la mascota. Pide una foto actual o una seña que no esté en el reporte antes de encontrarte con alguien, y hazlo en un lugar público.',
            'related' => ['directory' => 'veterinaria'],
        ],

        'encontre-una-mascota' => [
            'topic' => 'perdidas',
            'emoji' => '🤗',
            'title' => 'Encontré una mascota: cómo ayudarla a volver a casa',
            'summary' => 'Qué hacer para que vuelva con su familia de forma segura.',
            'minutes' => 3,
            'steps' => [
                'Acércate despacio, de lado y sin mirarla fijamente. Si está asustada o agresiva, no la fuerces: toma una foto y marca el lugar.',
                'Revisa si tiene placa o collar con un número de teléfono.',
                'Llévala a una veterinaria cercana para que lean si tiene microchip (suele ser gratis) y revisen que esté bien.',
                'Publica el reporte de mascota encontrada en PetRescue. Te mostraremos los reportes de mascotas perdidas que se parecen.',
                'Comparte el reporte en grupos del barrio. No publiques todas las señas: guárdate una para confirmar quién es la familia.',
                'Si la tienes en casa, dale agua, un lugar tranquilo y comida suave. Mantenla separada de tus otras mascotas hasta que un veterinario la revise.',
            ],
            'warning' => 'Antes de entregarla, pide fotos antiguas de la mascota con su familia, el carné de vacunas o que te digan una seña que no esté publicada.',
            'related' => ['directory' => 'veterinaria'],
        ],

        'prevenir-perdidas' => [
            'topic' => 'perdidas',
            'emoji' => '🏷️',
            'title' => 'Cómo evitar que tu mascota se pierda',
            'summary' => 'Pequeños hábitos que hacen la diferencia y facilitan el reencuentro.',
            'minutes' => 2,
            'steps' => [
                'Ponle un collar cómodo con placa y tu número de celular, aunque viva dentro de casa.',
                'Crea su perfil digital en PetRescue e imprime el código QR para la placa: quien la encuentre verá cómo contactarte.',
                'Pregunta en tu veterinaria por el microchip: es una identificación permanente.',
                'Revisa puertas, rejas y ventanas. Los gatos y los perros pequeños se escapan por espacios muy pequeños.',
                'Pasea siempre con correa en la calle, sobre todo cerca de avenidas o en lugares con mucha gente.',
                'Esteriliza a tu mascota: disminuye las escapadas en busca de pareja.',
                'Toma fotos recientes de cuerpo entero y de sus señas particulares, para tenerlas a mano si algún día las necesitas.',
            ],
            'warning' => null,
            'related' => ['directory' => 'veterinaria'],
        ],

        'golpe-de-calor' => [
            'topic' => 'emergencias',
            'emoji' => '🌡️',
            'title' => 'Golpe de calor: cómo reconocerlo y qué hacer',
            'summary' => 'Es una emergencia. Actúa rápido y llévala al veterinario.',
            'minutes' => 2,
            'steps' => [
                'Reconoce las señales: jadeo muy fuerte, babeo espeso, encías muy rojas o pálidas, vómito, debilidad, tambaleo o desmayo.',
                'Llévala de inmediato a la sombra o a un lugar fresco y con aire.',
                'Mójala con agua fresca (no helada), sobre todo en el cuello, las axilas, la barriga y las patas. Puedes usar toallas húmedas sin dejarlas encima todo el tiempo.',
                'Ofrécele agua fresca en poca cantidad, sin obligarla a tomar.',
                'Llama a tu veterinaria y llévala aunque parezca recuperada: el golpe de calor puede afectar órganos internos.',
            ],
            'warning' => 'Nunca dejes a una mascota dentro de un carro cerrado, ni por pocos minutos. Los perros de hocico corto (pug, bulldog, shih tzu) se afectan más rápido.',
            'related' => ['directory' => 'veterinaria', 'community' => 'salud'],
        ],

        'intoxicacion' => [
            'topic' => 'emergencias',
            'emoji' => '☠️',
            'title' => 'Sospecha de intoxicación',
            'summary' => 'Si crees que comió algo tóxico, cada minuto cuenta.',
            'minutes' => 2,
            'steps' => [
                'Retira lo que estaba comiendo y aleja a otras mascotas.',
                'Llama a tu veterinaria de inmediato y cuéntale qué comió, cuánto y hace cuánto tiempo.',
                'Guarda el empaque, la etiqueta o una muestra para mostrárselos al veterinario.',
                'Fíjate en señales como vómito, diarrea, temblores, convulsiones, mucha saliva, debilidad o dificultad para respirar.',
                'Llévala a la veterinaria aunque todavía no tenga síntomas: algunos tóxicos tardan horas en hacer efecto.',
            ],
            'warning' => 'No le des leche, aceite, sal ni remedios caseros, y no la hagas vomitar a menos que el veterinario te lo indique: con algunas sustancias el vómito empeora el daño.',
            'related' => ['directory' => 'veterinaria'],
        ],

        'alimentos-prohibidos' => [
            'topic' => 'alimentacion',
            'emoji' => '🚫',
            'title' => 'Alimentos y productos que nunca debes darles',
            'summary' => 'Cosas comunes en casa que son tóxicas para perros y gatos.',
            'minutes' => 2,
            'steps' => [
                'Chocolate y cacao (más peligroso entre más oscuro), café y bebidas energéticas.',
                'Uvas y uvas pasas: en perros pueden causar daño en los riñones.',
                'Cebolla, ajo, cebollín y puerro, crudos o cocinados (también en sopas y salsas).',
                'Xilitol, el endulzante de chicles, dulces y productos "sin azúcar".',
                'Alcohol, masa cruda con levadura y huesos cocinados (se astillan).',
                'Aguacate, sobre todo para aves, conejos y roedores.',
                'Medicamentos para personas: el acetaminofén y el ibuprofeno son muy tóxicos, en especial para gatos. Nunca los mediques sin fórmula veterinaria.',
                'Lirios y azucenas: una mínima cantidad (incluso el polen) es muy grave para los gatos.',
            ],
            'warning' => 'Si comió algo de esta lista, sigue la guía "Sospecha de intoxicación" y llama a tu veterinaria.',
            'related' => ['community' => 'alimentacion'],
        ],

        'vacunas-y-desparasitacion' => [
            'topic' => 'salud',
            'emoji' => '💉',
            'title' => 'Vacunas y desparasitación: lo básico',
            'summary' => 'Lo que debes tener presente para mantener su carné al día.',
            'minutes' => 3,
            'steps' => [
                'Cachorros y gatitos empiezan su plan de vacunas en las primeras semanas de vida; el veterinario te dirá cuándo aplicar cada dosis y cada refuerzo.',
                'Perros: las vacunas básicas protegen contra moquillo, parvovirus y hepatitis, entre otras, y se refuerzan según indique el veterinario.',
                'Gatos: la vacuna triple felina protege contra enfermedades respiratorias y la panleucopenia.',
                'La vacuna antirrábica es para perros y gatos, y se refuerza periódicamente. Pregunta por las jornadas gratuitas de vacunación en Pasto.',
                'Desparasitación interna: en general cada 3 a 6 meses en adultos y más seguido en cachorros, según tu veterinario.',
                'Desparasitación externa (pulgas y garrapatas): usa productos veterinarios para su especie y su peso. Nunca uses en gatos un producto para perros.',
                'Guarda el carné de vacunas: lo piden en guarderías, peluquerías, viajes y te sirve para demostrar que es tuya.',
            ],
            'warning' => 'Estas son indicaciones generales. El plan exacto depende de la edad, la especie y la salud de tu mascota: consúltalo con tu veterinario.',
            'related' => ['directory' => 'veterinaria', 'community' => 'salud'],
        ],

        'polvora-y-ruidos' => [
            'topic' => 'convivencia',
            'emoji' => '🎆',
            'title' => 'Pólvora y ruidos fuertes: cómo protegerla',
            'summary' => 'En diciembre, Año Nuevo y el Carnaval de Negros y Blancos se pierden más mascotas.',
            'minutes' => 2,
            'steps' => [
                'Esos días mantenla dentro de casa, con puertas y ventanas cerradas, y verifica que tenga su placa puesta.',
                'Prepárale un refugio tranquilo: una habitación interior con su cama, agua y una manta para esconderse.',
                'Pon música o la televisión a volumen moderado para disimular los estallidos.',
                'Quédate cerca y actúa con calma. Si busca consuelo, acompáñala; no la regañes por tener miedo.',
                'Sácala a pasear temprano, antes de que empiece la pólvora, y siempre con correa.',
                'Si su miedo es muy fuerte, habla con tu veterinario con semanas de anticipación. No le des tranquilizantes por tu cuenta.',
            ],
            'warning' => 'Si se escapa, publica el reporte de inmediato: la mayoría corre sin rumbo y se aleja de la casa.',
            'related' => ['community' => 'reactivas'],
        ],

        'primeros-dias-adoptado' => [
            'topic' => 'convivencia',
            'emoji' => '🏠',
            'title' => 'Los primeros días de una mascota adoptada o rescatada',
            'summary' => 'Cómo ayudarla a sentirse segura en su nuevo hogar.',
            'minutes' => 3,
            'steps' => [
                'Antes de que llegue, ten listo su espacio: cama, agua, comida, arenero (gatos) y juguetes.',
                'Los primeros días dale espacio. Deja que explore a su ritmo y no la obligues a recibir visitas.',
                'Mantén horarios fijos de comida, paseos y descanso: la rutina le da seguridad.',
                'Preséntale a las otras mascotas de la casa poco a poco y siempre supervisando.',
                'Llévala al veterinario la primera semana para un chequeo, vacunas y desparasitación.',
                'Ponle collar con placa desde el primer día: las mascotas recién llegadas se escapan con más facilidad porque todavía no conocen su casa.',
                'Ten paciencia. Puede tardar semanas o meses en mostrar su personalidad. Premia lo que hace bien en vez de castigar.',
            ],
            'warning' => null,
            'related' => ['community' => 'rescatadas'],
        ],

    ],

];
