<?php

namespace Database\Seeders;

use App\Models\PetReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * DATOS DE PRUEBA: reportes de mascotas perdidas y encontradas en Pasto,
 * Nariño, cada uno con una foto acorde a su especie, raza y color.
 * Las fotos están en public/images/seed/ (créditos en CREDITS.md de esa
 * carpeta). Los nombres y teléfonos son ficticios.
 */
class PetReportSeeder extends Seeder
{
    public function run(): void
    {
        $reports = [
            // ---------------------------------------------------------------- Perdidas
            [
                'type' => 'perdida', 'pet_name' => 'Max', 'species' => 'perro', 'breed' => 'Labrador',
                'color' => 'crema', 'size' => 'grande', 'sex' => 'macho', 'days_ago' => 1,
                'description' => 'Se salió de la casa cuando abrimos el portón. Es muy cariñoso, lleva collar café con placa y responde a su nombre. Le tiene miedo a la pólvora.',
                'photo' => 'labrador', 'lat' => 1.2165, 'lng' => -77.2862, 'place' => 'Parque Bolívar',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Luna', 'species' => 'perro', 'breed' => 'Husky',
                'color' => 'gris,blanco', 'size' => 'grande', 'sex' => 'hembra', 'days_ago' => 3,
                'description' => 'Se soltó de la correa durante el paseo. Tiene ojos azules y es muy juguetona, pero se asusta con los carros. Está esterilizada.',
                'photo' => 'husky', 'lat' => 1.2290, 'lng' => -77.2930, 'place' => 'Barrio Torobajo, cerca a la Universidad de Nariño',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Rocky', 'species' => 'perro', 'breed' => 'Schnauzer',
                'color' => 'gris', 'size' => 'pequeño', 'sex' => 'macho', 'days_ago' => 2,
                'description' => 'Se perdió en la plaza mientras hacíamos mercado. Tiene collar naranja y está recién motilado. Es mayor (9 años) y necesita medicamento para el corazón.',
                'photo' => 'schnauzer', 'lat' => 1.2118, 'lng' => -77.2779, 'place' => 'Plaza de mercado El Potrerillo',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Bella', 'species' => 'perro', 'breed' => 'Golden retriever',
                'color' => 'crema', 'size' => 'grande', 'sex' => 'hembra', 'days_ago' => 5,
                'description' => 'Se escapó del conjunto. Es muy mansa y se acerca a todo el mundo, lleva un arnés café. Ofrecemos recompensa.',
                'photo' => 'golden-retriever', 'lat' => 1.2240, 'lng' => -77.2810, 'place' => 'Conjunto residencial en el barrio Palermo',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Toby', 'species' => 'perro', 'breed' => 'Beagle',
                'color' => 'cafe,blanco,negro', 'size' => 'mediano', 'sex' => 'macho', 'days_ago' => 0,
                'description' => 'Siguió un olor y se metió por un potrero. Ladra mucho y es muy curioso. Tiene un microchip.',
                'photo' => 'beagle', 'lat' => 1.1975, 'lng' => -77.2742, 'place' => 'Barrio Lorenzo de Aldana',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Canelo', 'species' => 'perro', 'breed' => 'Criollo / mestizo',
                'color' => 'cafe', 'size' => 'mediano', 'sex' => 'macho', 'days_ago' => 7,
                'description' => 'Es un perrito adoptado, muy tímido con extraños. Se perdió después de los fuegos artificiales. Tiene una cicatriz pequeña en la oreja izquierda.',
                'photo' => 'perro-criollo-cafe', 'lat' => 1.2055, 'lng' => -77.2685, 'place' => 'Barrio Santa Mónica',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Mishi', 'species' => 'gato', 'breed' => 'Siamés',
                'color' => 'crema,cafe', 'size' => null, 'sex' => 'hembra', 'days_ago' => 4,
                'description' => 'Gata siamesa de ojos azules, nunca sale de la casa y se escapó por una ventana. Es asustadiza; si la ven, por favor no la persigan y avísennos.',
                'photo' => 'gato-siames', 'lat' => 1.2185, 'lng' => -77.2758, 'place' => 'Barrio Las Cuadras',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Garfield', 'species' => 'gato', 'breed' => 'Criollo / mestizo',
                'color' => 'naranja', 'size' => null, 'sex' => 'macho', 'days_ago' => 6,
                'description' => 'Gato naranja grande y algo gordito, castrado. Le encanta dormir en los techos. Lleva collar rojo con cascabel.',
                'photo' => 'gato-naranja', 'lat' => 1.2210, 'lng' => -77.2705, 'place' => 'Barrio La Carolina',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Copito', 'species' => 'conejo', 'breed' => 'Belier (orejas caídas)',
                'color' => 'blanco', 'size' => null, 'sex' => 'macho', 'days_ago' => 2,
                'description' => 'Conejo belier blanco de orejas caídas (en la foto está a la izquierda, junto a su hermana). Se salió del patio; es dócil y come zanahoria de la mano.',
                'photo' => 'conejo-belier', 'lat' => 1.2330, 'lng' => -77.2860, 'place' => 'Barrio Aranda',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Kiwi', 'species' => 'ave', 'breed' => 'Periquito australiano',
                'color' => 'verde,amarillo', 'size' => null, 'sex' => 'macho', 'days_ago' => 1,
                'description' => 'Periquito verde con cabeza amarilla. Se escapó al limpiar la jaula. Dice "hola" y silba; suele posarse en árboles bajos.',
                'photo' => 'periquito', 'lat' => 1.2095, 'lng' => -77.2900, 'place' => 'Sector la Panamericana',
            ],
            [
                'type' => 'perdida', 'pet_name' => 'Canela', 'species' => 'roedor', 'breed' => 'Cobayo',
                'color' => 'cafe,blanco,negro', 'size' => null, 'sex' => 'hembra', 'days_ago' => 3,
                'description' => 'Cobaya tricolor, mascota de mis hijos. Se perdió en el jardín del frente; puede estar escondida entre matorrales.',
                'photo' => 'cobayo', 'lat' => 1.2020, 'lng' => -77.2815, 'place' => 'Barrio El Ejido',
            ],

            // -------------------------------------------------------------- Encontradas
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'perro', 'breed' => 'Criollo / mestizo',
                'color' => 'negro,blanco', 'size' => 'mediano', 'sex' => 'hembra', 'days_ago' => 1,
                'description' => 'La encontré deambulando sola, tiene collar rojo pero sin placa. Está bien cuidada y es muy amigable. La tengo en mi casa temporalmente.',
                'photo' => 'perro-criollo-blanco-negro', 'lat' => 1.2140, 'lng' => -77.2800, 'place' => 'Cerca al parque Nariño',
            ],
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'perro', 'breed' => 'Pug',
                'color' => 'crema', 'size' => 'pequeño', 'sex' => 'macho', 'days_ago' => 0,
                'description' => 'Pug encontrado en la avenida, estaba desorientado y con mucha sed. No tiene collar. Lo tengo resguardado.',
                'photo' => 'pug', 'lat' => 1.2255, 'lng' => -77.2890, 'place' => 'Avenida Los Estudiantes',
            ],
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'perro', 'breed' => 'Chihuahua',
                'color' => 'cafe', 'size' => 'pequeño', 'sex' => 'no_se', 'days_ago' => 2,
                'description' => 'Estaba refugiado bajo un carro durante la lluvia, temblando. Tiene collar negro. Es muy nervioso pero ya se dejó cargar.',
                'photo' => 'chihuahua', 'lat' => 1.2100, 'lng' => -77.2830, 'place' => 'Centro, cerca a la Catedral',
            ],
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'perro', 'breed' => 'Pastor alemán',
                'color' => 'negro,cafe', 'size' => 'grande', 'sex' => 'macho', 'days_ago' => 4,
                'description' => 'Pastor alemán adulto, lleva collar con medalla pero sin datos. Obedece órdenes básicas, claramente tiene dueño.',
                'photo' => 'pastor-aleman', 'lat' => 1.1950, 'lng' => -77.2880, 'place' => 'Vía a Obonuco',
            ],
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'perro', 'breed' => 'Pitbull',
                'color' => 'gris', 'size' => 'grande', 'sex' => 'macho', 'days_ago' => 3,
                'description' => 'Lo encontramos amarrado a un poste frente a una tienda, con arnés negro. Es tranquilo y sociable con otros perros.',
                'photo' => 'pitbull', 'lat' => 1.2175, 'lng' => -77.2655, 'place' => 'Barrio San Vicente',
            ],
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'perro', 'breed' => 'French poodle',
                'color' => 'blanco', 'size' => 'pequeño', 'sex' => 'hembra', 'days_ago' => 1,
                'description' => 'Perrita poodle blanca, recién bañada y con moño. Andaba sola por el centro comercial. Parece de casa.',
                'photo' => 'french-poodle', 'lat' => 1.2080, 'lng' => -77.2940, 'place' => 'Cerca al centro comercial Unicentro',
            ],
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'gato', 'breed' => 'Criollo / mestizo',
                'color' => 'negro', 'size' => null, 'sex' => 'no_se', 'days_ago' => 2,
                'description' => 'Gato negro de ojos amarillos, muy limpio y manso. Lleva dos días maullando en la puerta de mi edificio.',
                'photo' => 'gato-negro', 'lat' => 1.2225, 'lng' => -77.2780, 'place' => 'Barrio San Ignacio',
            ],
            [
                'type' => 'encontrada', 'pet_name' => null, 'species' => 'gato', 'breed' => config('pets.unknown_breed'),
                'color' => 'atigrado', 'size' => null, 'sex' => 'hembra', 'days_ago' => 5,
                'description' => 'Gata atigrada joven con collar. Apareció en mi patio y no se quiere ir; está comiendo bien.',
                'photo' => 'gato-atigrado', 'lat' => 1.2060, 'lng' => -77.2760, 'place' => 'Cerca a la Universidad Mariana',
                'status' => 'reunido', // ejemplo de caso ya resuelto: no aparece en el listado público
            ],
        ];

        foreach ($reports as $i => $r) {
            PetReport::forceCreate([
                // DatabaseSeeder usa WithoutModelEvents, así que el token y el
                // estado se asignan aquí en vez de en PetReport::booted().
                'management_token' => (string) Str::uuid(),
                'type' => $r['type'],
                'species' => $r['species'],
                'breed' => $r['breed'],
                'color' => $r['color'],
                'size' => $r['size'],
                'sex' => $r['sex'],
                'event_date' => now()->subDays($r['days_ago'])->toDateString(),
                'pet_name' => $r['pet_name'],
                'description' => $r['description'],
                'photo_path' => "images/seed/{$r['photo']}.jpg",
                'contact_phone' => sprintf('3150000%03d', $i + 1),
                'contact_email' => $i % 2 === 0 ? "contacto{$i}@example.com" : null,
                'contact_whatsapp' => true,
                'latitude' => $r['lat'],
                'longitude' => $r['lng'],
                'location_reference' => $r['place'],
                'status' => $r['status'] ?? 'activo',
                'created_at' => now()->subDays($r['days_ago'])->subHours($i),
            ]);
        }
    }
}
