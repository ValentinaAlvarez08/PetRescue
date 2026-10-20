<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sprint 3 — HU-17 / HU-18: DATOS DE EJEMPLO para los espacios de la
 * comunidad (publicaciones y comentarios). Los apodos e historias son
 * ficticios. Las fotos se reutilizan de public/images/seed/.
 *
 * Si ya hay publicaciones, no hace nada, para poder ejecutarlo con
 * `php artisan db:seed --class=CommunitySeeder` sin duplicar datos.
 */
class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        if (Post::exists()) {
            $this->command?->info('CommunitySeeder: ya hay publicaciones, no se agregó nada.');

            return;
        }

        $posts = [
            // ---------------------------------------------------------------- Rescatadas
            [
                'topic' => 'rescatadas', 'nickname' => 'Mamá de Canela', 'hours_ago' => 5, 'helpful' => 12,
                'photo' => 'perro-criollo-cafe',
                'title' => 'Adopté a Canela hace una semana y no sale de debajo de la cama',
                'body' => "La rescatamos de la calle cerca de la plaza del Potrerillo. Come bien y ya hace sus necesidades afuera, pero se pasa el día escondida debajo de la cama y tiembla cuando alguien se acerca.\n\n¿Cuánto tiempo les tomó a sus perros rescatados sentirse en confianza? ¿Hay algo que pueda hacer para ayudarla sin presionarla?",
                'comments' => [
                    ['Andrés M.', 'A mi perro le tomó casi un mes. Lo que más sirvió fue no intentar sacarlo, sino sentarme cerca a leer y dejarle premios en el piso. Él solito empezó a acercarse.'],
                    ['Fundación Huellas (voluntaria)', 'Es completamente normal. Respeta su escondite como su lugar seguro, mantén horarios fijos de comida y paseo y evita visitas ruidosas las primeras semanas. La regla que usamos es 3 días, 3 semanas, 3 meses.'],
                    ['Mamá de Canela', '¡Gracias! Hoy le dejé pollo cocido junto a la cama y salió a comérselo frente a mí. Pequeños pasos 🧡'],
                ],
            ],
            [
                'topic' => 'rescatadas', 'nickname' => 'Julián', 'hours_ago' => 30, 'helpful' => 7,
                'photo' => 'gato-negro',
                'title' => 'Gato rescatado que bufa a mi otra gata',
                'body' => 'Recogí un gatito negro de unos 4 meses en Torobajo. Mi gata de 3 años le bufa y le gruñe cada vez que lo ve. Los tengo separados en cuartos distintos desde hace 5 días. ¿Cuándo los puedo juntar?',
                'comments' => [
                    ['Valeria', 'Intercambia sus mantas para que se acostumbren al olor antes de verse. Luego déjalos verse por una puerta entreabierta mientras comen. A mí me funcionó en dos semanas.'],
                    ['Diana', 'Y no olvides llevar al gatito al veterinario antes de juntarlos, por si trae pulgas u hongos.'],
                ],
            ],
            [
                'topic' => 'rescatadas', 'nickname' => 'Carolina R.', 'hours_ago' => 75, 'helpful' => 3,
                'title' => '¿Qué llevar al veterinario la primera vez con un perro rescatado?',
                'body' => 'Mañana llevo por primera vez al perrito que encontré. No sé su edad ni si está vacunado. ¿Qué le pido al veterinario y qué preguntas debería hacer?',
                'comments' => [
                    ['Papá de Rocky', 'Pide revisión general, desparasitación interna y externa, y que te diga la edad aproximada por los dientes. Luego arrancas el plan de vacunas.'],
                ],
            ],

            // ---------------------------------------------------------------- Reactivas
            [
                'topic' => 'reactivas', 'nickname' => 'Laura y Toby', 'hours_ago' => 2, 'helpful' => 18,
                'photo' => 'husky',
                'title' => '¿Cómo lograron que su perro dejara de ladrarle a otros perros en el paseo?',
                'body' => "Toby es un husky de 2 años. En la casa es un amor, pero en la calle se pone como loco cuando ve otro perro: ladra, tira de la correa y se para en dos patas. Ya me da vergüenza sacarlo al parque Infantil.\n\n¿Alguien ha trabajado la reactividad con un entrenador en Pasto o tiene ejercicios que les hayan funcionado?",
                'comments' => [
                    ['Sofía G.', 'Lo primero es aumentar la distancia: si lo ves venir, cruza la calle antes de que Toby reaccione y prémialo cuando mira al otro perro sin ladrar. Con el tiempo la distancia se va acortando.'],
                    ['Camilo', 'Pasea en horarios con menos perros (temprano en la mañana) mientras lo trabajas. Y un arnés de enganche frontal ayuda mucho con los tirones.'],
                    ['Laura y Toby', 'Lo del arnés frontal no lo sabía, ¡voy a probar! Gracias.'],
                    ['Andrés M.', 'Nosotros contratamos a un etólogo y valió la pena. No es un problema de "mala crianza", es miedo o frustración y se puede trabajar.'],
                ],
            ],
            [
                'topic' => 'reactivas', 'nickname' => 'Mateo', 'hours_ago' => 50, 'helpful' => 9,
                'title' => 'Mi perra le tiene pánico a la pólvora en diciembre',
                'body' => 'Se acerca diciembre y el año pasado mi perra Kira se escapó por el miedo a la pólvora. Quiero prepararme desde ya. ¿Qué les ha funcionado: camisetas de presión, música, medicamentos?',
                'comments' => [
                    ['Diana', 'Arma un "refugio" en el cuarto más interno de la casa, con su cama y una manta encima. Cierra cortinas y pon música o la TV. Y que siempre tenga la placa con tu número.'],
                    ['Valeria', 'Consulta con tu veterinario con tiempo: hay calmantes naturales y otros con fórmula, pero hay que probarlos antes, no el mismo 31.'],
                ],
            ],
            [
                'topic' => 'reactivas', 'nickname' => 'Ana Lucía', 'hours_ago' => 120, 'helpful' => 2,
                'title' => 'Mi gato ataca los tobillos de las visitas',
                'body' => 'Tengo un gato atigrado de un año que se esconde y luego se lanza a los pies de las visitas. No es juego, a veces muerde fuerte. ¿Es miedo? ¿Cómo lo corrijo?',
                'comments' => [],
            ],

            // ---------------------------------------------------------------- Entrenamiento
            [
                'topic' => 'entrenamiento', 'nickname' => 'Papá de Rocky', 'hours_ago' => 8, 'helpful' => 6,
                'photo' => 'beagle',
                'title' => 'Truco que nos sirvió para que dejara de orinar dentro de la casa',
                'body' => "Rocky tiene 5 meses y llevábamos semanas limpiando orines en la sala. Lo que nos funcionó: sacarlo apenas se despierta, después de cada comida y después de jugar, siempre al mismo lugar, y premiarlo con un trocito de salchicha justo cuando termina afuera.\n\nEn diez días pasamos de 4 accidentes diarios a casi ninguno. Lo comparto por si le sirve a alguien.",
                'comments' => [
                    ['Mamá de Luna', '¡Qué bien! Agrego: limpiar con vinagre o un limpiador enzimático, porque si queda el olor vuelve al mismo sitio.'],
                ],
            ],
            [
                'topic' => 'entrenamiento', 'nickname' => 'Sebastián', 'hours_ago' => 96, 'helpful' => 4,
                'title' => '¿A qué edad se puede empezar a enseñar a caminar con correa?',
                'body' => 'Tengo un cachorro labrador de 3 meses y aún no tiene todas las vacunas. ¿Puedo ir enseñándole la correa dentro de la casa o es mejor esperar?',
                'comments' => [
                    ['Camilo', 'Puedes empezar desde ya dentro de la casa: que se acostumbre al collar y a la correa con juegos y premios. Así cuando salga ya va a tener la base.'],
                ],
            ],

            // ---------------------------------------------------------------- Salud
            [
                'topic' => 'salud', 'nickname' => 'Diana', 'hours_ago' => 12, 'helpful' => 10,
                'title' => '¿Cada cuánto desparasitan a sus perros y gatos?',
                'body' => 'Mi veterinario dice que cada tres meses, pero una amiga me dijo que cada seis. Tengo un perro y dos gatos que salen al patio. ¿Ustedes cada cuánto lo hacen y con qué producto?',
                'comments' => [
                    ['Valeria', 'Para mascotas que salen, cada 3 meses es lo más común. Lo importante es pesarlos antes, porque la dosis depende del peso.'],
                    ['Julián', 'Y no olvides lo externo (pulgas y garrapatas), que es aparte. Nosotros usamos pipeta cada mes.'],
                ],
            ],
            [
                'topic' => 'salud', 'nickname' => 'Mamá de Luna', 'hours_ago' => 60, 'helpful' => 5,
                'photo' => 'gato-siames',
                'title' => 'Experiencia esterilizando a mi gata en una jornada gratuita',
                'body' => 'Quería contar que esterilicé a Luna en una jornada de la Alcaldía. Me dieron instrucciones claras: ayuno de 12 horas, collar isabelino y reposo una semana. Se recuperó súper bien. Si tienen dudas sobre esterilizar, ¡anímense!',
                'comments' => [
                    ['Ana Lucía', '¿Sabes cada cuánto hacen esas jornadas? Quiero llevar a mi gato.'],
                    ['Mamá de Luna', 'Hacen varias al año, normalmente las anuncian en las redes de la Alcaldía de Pasto. Hay que inscribirse con anticipación porque los cupos se llenan rápido.'],
                ],
            ],

            // ---------------------------------------------------------------- Alimentación
            [
                'topic' => 'alimentacion', 'nickname' => 'Camilo', 'hours_ago' => 20, 'helpful' => 8,
                'title' => 'Alimentos de la casa que NUNCA hay que darle a un perro',
                'body' => "Hago esta lista porque en navidad mi perro casi se intoxica con natilla con uvas pasas:\n\n- Chocolate y café\n- Uvas y uvas pasas\n- Cebolla y ajo (también en salsas)\n- Huesos de pollo cocinados\n- Aguacate\n- Xilitol (chicles y dulces sin azúcar)\n\n¿Qué más agregarían?",
                'comments' => [
                    ['Sofía G.', 'El alcohol y la masa cruda de pan. Y en gatos, el lirio es muy tóxico aunque sea solo una planta.'],
                    ['Diana', 'Gracias por la lista, la imprimí y la pegué en la nevera.'],
                ],
            ],
            [
                'topic' => 'alimentacion', 'nickname' => 'Natalia', 'hours_ago' => 140, 'helpful' => 1,
                'photo' => 'conejo-belier',
                'title' => '¿Qué verduras puede comer un conejo?',
                'body' => 'Adopté un conejo belier y no tengo claro qué darle además del heno. ¿Zanahoria todos los días está bien o es demasiado?',
                'comments' => [
                    ['Valeria', 'La zanahoria es como un dulce para ellos, mejor poquita y no todos los días. La base es heno ilimitado y hojas verdes como cilantro, perejil y lechuga romana (no la lechuga crespa).'],
                ],
            ],

            // ---------------------------------------------------------------- Adopción
            [
                'topic' => 'adopcion', 'nickname' => 'Felipe', 'hours_ago' => 36, 'helpful' => 6,
                'title' => '¿Qué preguntas hacer antes de adoptar en una fundación?',
                'body' => 'Mi familia y yo queremos adoptar un perro adulto. Vivimos en apartamento y trabajamos de día. ¿Qué deberíamos preguntarle a la fundación para escoger bien y no tener que devolverlo?',
                'comments' => [
                    ['Fundación Huellas (voluntaria)', 'Pregunta por su nivel de energía, si convive con gatos o niños, si se queda tranquilo solo y su historial de salud. Un perro adulto tranquilo suele adaptarse muy bien a apartamento.'],
                    ['Carolina R.', 'También pregunta si puedes hacer una visita o un "fin de semana de prueba". Varias fundaciones lo permiten.'],
                ],
            ],
            [
                'topic' => 'adopcion', 'nickname' => 'Valeria', 'hours_ago' => 160, 'helpful' => 11,
                'photo' => 'pitbull',
                'title' => 'Adoptamos a un pitbull adulto y es el perro más noble del mundo',
                'body' => 'Mucha gente nos dijo que no adoptáramos un pitbull "por peligroso". Lleva un año con nosotros y es dulce con mis hijos y con el gato. Los prejuicios hacen que estos perros pasen años en los refugios. Si están pensando adoptar, ¡denles una oportunidad!',
                'comments' => [
                    ['Felipe', 'Qué bonita historia. ¿De qué fundación lo adoptaron?'],
                    ['Valeria', 'De una fundación pequeña en Catambuco. Te paso el contacto si quieres.'],
                ],
            ],
        ];

        foreach ($posts as $p) {
            $createdAt = now()->subHours($p['hours_ago']);

            // DatabaseSeeder usa WithoutModelEvents: el token se asigna aquí.
            $post = Post::forceCreate([
                'management_token' => (string) Str::uuid(),
                'topic' => $p['topic'],
                'nickname' => $p['nickname'],
                'title' => $p['title'],
                'body' => $p['body'],
                'photo_path' => isset($p['photo']) ? "images/seed/{$p['photo']}.jpg" : null,
                'helpful_count' => $p['helpful'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($p['comments'] as $i => [$nickname, $body]) {
                $at = $createdAt->copy()->addMinutes(40 * ($i + 1));
                Comment::forceCreate([
                    'post_id' => $post->id,
                    'nickname' => $nickname,
                    'body' => $body,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
            }
        }
    }
}
