<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

/**
 * Sprint 2 — HU-16: DATOS DE EJEMPLO para el directorio (Pasto, Nariño).
 * Los nombres, teléfonos y direcciones son ficticios; reemplazarlos por
 * negocios reales (con su autorización) antes de publicar la plataforma.
 */
class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $places = [
            ['Huellitas Pet Shop', 'tienda', 'Tienda de concentrado y accesorios para perros y gatos. Domicilios en todo Pasto.', 'Concentrado, Snacks, Collares, Camas, Domicilios', 'Cra. 27 # 18-40, Centro', 'Lun a sáb 8:00 a.m. – 7:00 p.m.', 1.2142, -77.2789, true],
            ['Mundo Animal Nariño', 'tienda', 'Alimento para mascotas, arena para gatos y juguetes.', 'Concentrado, Arena, Juguetes', 'Calle 18 # 32-15, Las Cuadras', 'Todos los días 9:00 a.m. – 8:00 p.m.', 1.2185, -77.2835, false],
            ['Patitas Felices Market', 'tienda', 'Tienda de mascotas con productos naturales y alimento BARF.', 'Alimento natural, BARF, Accesorios', 'Av. Panamericana # 12-30', 'Lun a sáb 9:00 a.m. – 6:00 p.m.', 1.2078, -77.2902, true],
            ['Guardería Colitas Contentas', 'guarderia', 'Guardería canina por días con zona verde, juegos supervisados y transporte.', 'Guardería por día, Transporte, Paseos', 'Vía Chachagüí km 2', 'Lun a vie 7:00 a.m. – 6:00 p.m.', 1.2291, -77.2744, true],
            ['Hotel Canino El Refugio', 'guarderia', 'Hospedaje para perros y gatos en vacaciones, con cámaras para ver a tu mascota.', 'Hospedaje, Cámaras 24/7, Paseos', 'Barrio Aranda, Calle 5 # 3-20', 'Todos los días 24 horas', 1.2251, -77.2618, false],
            ['Guardería Gatuna Miau', 'guarderia', 'Cuidado y hospedaje exclusivo para gatos, en un ambiente tranquilo y sin perros.', 'Hospedaje felino, Cuidado por día', 'Cra. 22 # 9-11, Palermo', 'Lun a dom 8:00 a.m. – 6:00 p.m.', 1.2107, -77.2701, true],
            ['Spa Canino Burbujas', 'peluqueria', 'Baño, corte de pelo según la raza, corte de uñas y limpieza de oídos.', 'Baño, Corte, Uñas, Limpieza de oídos', 'Calle 20 # 24-55, San Andrés', 'Mar a dom 9:00 a.m. – 6:00 p.m.', 1.2163, -77.2752, true],
            ['Peluquería Pelitos', 'peluqueria', 'Estética canina y felina con productos hipoalergénicos. Servicio a domicilio.', 'Baño, Deslanado, Domicilio', 'Cra. 40 # 19-08, La Aurora', 'Lun a sáb 8:00 a.m. – 5:00 p.m.', 1.2211, -77.2911, true],
            ['Clínica Veterinaria San Francisco', 'veterinaria', 'Consulta general, vacunación, cirugía y urgencias 24 horas.', 'Consulta, Vacunas, Cirugía, Urgencias 24h', 'Calle 16 # 28-12, Centro', 'Todos los días 24 horas', 1.2128, -77.2821, true],
            ['Veterinaria Amigos Fieles', 'veterinaria', 'Medicina preventiva, esterilización y control de peso.', 'Consulta, Esterilización, Desparasitación', 'Cra. 19 # 21-06, Las Acacias', 'Lun a sáb 8:00 a.m. – 6:00 p.m.', 1.2197, -77.2699, false],
            ['Centro Veterinario Galeras', 'veterinaria', 'Especialistas en gatos y animales pequeños (conejos, hámsteres, aves).', 'Felinos, Exóticos, Laboratorio', 'Av. Boyacá # 8-45', 'Lun a vie 9:00 a.m. – 7:00 p.m.', 1.2044, -77.2787, true],
            ['Droguería Veterinaria VetFarma', 'drogueria', 'Medicamentos veterinarios con y sin fórmula, antipulgas y vitaminas.', 'Medicamentos, Antipulgas, Vitaminas', 'Calle 17 # 25-33, Centro', 'Lun a sáb 7:30 a.m. – 7:30 p.m.', 1.2136, -77.2768, true],
            ['Agro y Mascotas El Campo', 'drogueria', 'Droguería veterinaria y agropecuaria: desparasitantes, vacunas y concentrado.', 'Desparasitantes, Vacunas, Concentrado', 'Cra. 6 # 14-20, Potrerillo', 'Lun a sáb 7:00 a.m. – 6:00 p.m.', 1.2089, -77.2655, false],
        ];

        foreach ($places as $i => [$name, $category, $description, $services, $address, $schedule, $lat, $lng, $wa]) {
            Business::create([
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'services' => $services,
                'phone' => sprintf('300000%04d', $i + 1),
                'has_whatsapp' => $wa,
                'address' => $address.', Pasto',
                'schedule' => $schedule,
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }
    }
}
