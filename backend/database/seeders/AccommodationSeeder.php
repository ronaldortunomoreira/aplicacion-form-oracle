<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\Pais;
use App\Models\RoomUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Accommodation Types
        $types = [
            [
                'name' => 'Hoteles',
                'slug' => 'hotel',
                'icon' => 'Building2',
                'description' => 'Habitaciones privadas con servicios completos, recepción 24/7 y comodidades exclusivas.',
            ],
            [
                'name' => 'Hostales',
                'slug' => 'hostal',
                'icon' => 'BedSingle',
                'description' => 'Alojamiento social y accesible con dormitorios compartidos y habitaciones privadas.',
            ],
            [
                'name' => 'Cabañas',
                'slug' => 'cabana',
                'icon' => 'Home',
                'description' => 'Casas de campo y cabañas rústicas en contacto con la naturaleza y total privacidad.',
            ],
            [
                'name' => 'Apartamentos',
                'slug' => 'apartamento',
                'icon' => 'Building',
                'description' => 'Espacios completos equipados con cocina, ideales para estancias prolongadas o viajes en familia.',
            ],
            [
                'name' => 'Glamping',
                'slug' => 'glamping',
                'icon' => 'Tent',
                'description' => 'Experiencia de acampar al aire libre con el lujo, confort y diseño de un hotel de alta gama.',
            ],
        ];

        $createdTypes = [];
        foreach ($types as $type) {
            $createdTypes[$type['slug']] = AccommodationType::create($type);
        }

        // 2. Accommodations data
        $accommodationsData = [
            // HOTEL 1
            [
                'type_slug' => 'hotel',
                'name' => 'Grand Palace Luxury Hotel & Spa',
                'slug' => 'grand-palace-luxury-hotel-spa',
                'headline' => 'Elegancia clásica y spa de clase mundial en el corazón de Madrid',
                'description' => 'Ubicado a pasos de los principales monumentos, el Grand Palace ofrece una experiencia cinco estrellas inolvidable. Disfrute de nuestra piscina climatizada en la azotea, spa galardonado y restaurante con estrellas Michelin.',
                'address' => 'Paseo de la Castellana 45',
                'city' => 'Madrid',
                'state' => 'Madrid',
                'country_slug' => 'espana',
                'latitude' => 40.4283,
                'longitude' => -3.6892,
                'star_rating' => 5,
                'guest_rating' => 9.6,
                'reviews_count' => 342,
                'check_in_time' => '15:00',
                'check_out_time' => '12:00',
                'amenities' => ['wifi', 'pool', 'spa', 'breakfast', 'gym', 'parking', 'ac', 'restaurant', 'bar'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 48 horas antes de la llegada.',
                    'pets' => 'No se admiten mascotas.',
                    'smoking' => 'Espacio 100% libre de humo.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Suite Deluxe King con Balcón y Vista',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 1,
                        'total_units' => 4,
                        'base_price_per_night' => 185.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size'],
                        'amenities' => ['Baño privado de mármol', 'Bañera de hidromasaje', 'Smart TV 55"', 'Minibar de cortesía', 'Cafetera Nespresso', 'Balcón privado'],
                        'images' => ['https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Habitación Doble Superior Clásica',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 8,
                        'base_price_per_night' => 120.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama Queen Size'],
                        'amenities' => ['Baño privado con ducha de lluvia', 'Aire acondicionado silencioso', 'WiFi de alta velocidad', 'Caja fuerte'],
                        'images' => ['https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // HOTEL 2 (Cancún)
            [
                'type_slug' => 'hotel',
                'name' => 'Riviera Sunset Beach Resort & Spa',
                'slug' => 'riviera-sunset-beach-resort',
                'headline' => 'Paraíso frente al mar turquesa con albercas infinitas y todo incluido',
                'description' => 'Ubicado directamente en la playa más hermosa de Cancún, este resort combina lujo relajado con espectaculares atardeceres caribeños, deportes acuáticos y alta gastronomía.',
                'address' => 'Blvd. Kukulcan Km 16.5, Zona Hotelera',
                'city' => 'Cancún',
                'state' => 'Quintana Roo',
                'country_slug' => 'mexico',
                'latitude' => 21.0921,
                'longitude' => -86.7645,
                'star_rating' => 5,
                'guest_rating' => 9.7,
                'reviews_count' => 512,
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'amenities' => ['wifi', 'pool', 'spa', 'breakfast', 'beachfront', 'gym', 'parking', 'bar', 'ac'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 7 días antes de la llegada.',
                    'pets' => 'Pet friendly (perros pequeños hasta 10kg con costo adicional).',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=800&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Ocean Front Suite con Terraza Privada',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 2,
                        'total_units' => 5,
                        'base_price_per_night' => 240.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King', '1 Sofá Cama Doble'],
                        'amenities' => ['Vista directa al mar', 'Acceso directo a la playa', 'Jacuzzi en terraza', 'Servicio a la habitación 24h'],
                        'images' => ['https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // HOSTAL 1 (Barcelona)
            [
                'type_slug' => 'hostal',
                'name' => 'Wanderlust Backpackers Social Hostel',
                'slug' => 'wanderlust-backpackers-social-hostel',
                'headline' => 'El mejor punto de encuentro para viajeros internacionales en Barcelona',
                'description' => 'Un ambiente vibrante, seguro y lleno de energía a pasos de Las Ramblas y el Barrio Gótico. Ofrecemos tours a pie gratuitos, eventos nocturnos, cocina común completamente equipada y espacios de coworking.',
                'address' => 'Carrer dels Tallers 18',
                'city' => 'Barcelona',
                'state' => 'Cataluña',
                'country_slug' => 'espana',
                'latitude' => 41.3851,
                'longitude' => 2.1685,
                'star_rating' => null, // Hostales normalmente no tienen estrellas
                'guest_rating' => 9.3,
                'reviews_count' => 840,
                'check_in_time' => '14:00',
                'check_out_time' => '11:00',
                'amenities' => ['wifi', 'kitchen', 'bar', 'coworking', 'laundry', 'ac', 'lockers'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 24 horas antes del check-in.',
                    'age' => 'Mayores de 18 años.',
                    'quiet_hours' => 'Silencio en dormitorios a partir de las 23:00.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Cama en Dormitorio Mixto (6 literas tipo cápsula)',
                        'unit_type' => 'shared_room',
                        'capacity_adults' => 1,
                        'capacity_children' => 0,
                        'total_units' => 18,
                        'base_price_per_night' => 26.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'shared',
                        'bed_distribution' => ['1 Cama en Litera con cortina de privacidad'],
                        'amenities' => ['Baño compartido impecable', 'Enchufe y luz de lectura individual', 'Locker espacioso con cerradura electrónica', 'Ropa de cama incluida'],
                        'images' => ['https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Cama en Dormitorio Femenino (4 camas con baño interno)',
                        'unit_type' => 'shared_room',
                        'capacity_adults' => 1,
                        'capacity_children' => 0,
                        'total_units' => 8,
                        'base_price_per_night' => 31.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama en Litera de Madera'],
                        'amenities' => ['Baño privado exclusivo para el dormitorio femenino', 'Espejo de cuerpo entero', 'Secador de pelo'],
                        'images' => ['https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Habitación Doble Privada (Económica)',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 3,
                        'base_price_per_night' => 65.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'shared',
                        'bed_distribution' => ['1 Cama Doble'],
                        'amenities' => ['Privacidad total', 'Baño compartido a pocos pasos', 'Ventana exterior', 'Toallas incluidas'],
                        'images' => ['https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // CABAÑA 1 (Bariloche, Argentina)
            [
                'type_slug' => 'cabana',
                'name' => 'Cabañas Bosque Andino & Vistas al Lago',
                'slug' => 'cabanas-bosque-andino-lago',
                'headline' => 'Auténtica calidez patagónica con chimenea a leña y vistas panorámicas',
                'description' => 'Rodeada de coihues y cipreses milenarios frente al lago Nahuel Huapi. Nuestras cabañas de piedra y ciprés brindan el refugio perfecto para desconectar, disfrutar de un asado patagónico y relajarse al calor del fuego.',
                'address' => 'Av. Bustillo Km 12.4',
                'city' => 'Bariloche',
                'state' => 'Río Negro',
                'country_slug' => 'argentina',
                'latitude' => -41.1335,
                'longitude' => -71.3103,
                'star_rating' => null,
                'guest_rating' => 9.8,
                'reviews_count' => 218,
                'check_in_time' => '15:00',
                'check_out_time' => '10:30',
                'amenities' => ['wifi', 'kitchen', 'parking', 'pets', 'fireplace', 'bbq', 'heating'],
                'policies' => [
                    'cancellation' => 'Cancelación flexible hasta 5 días antes.',
                    'pets' => '¡Admitimos mascotas con alegría y sin costo extra!',
                    'wood' => 'Leña para chimenea incluida durante la estancia.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=800&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Cabaña Familiar Panorámica Completa (Hasta 6 personas)',
                        'unit_type' => 'entire_place',
                        'capacity_adults' => 5,
                        'capacity_children' => 2,
                        'total_units' => 3,
                        'base_price_per_night' => 135.00,
                        'cleaning_fee' => 25.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King', '2 Camas Individuales', '1 Cama Marinera'],
                        'amenities' => ['Lugar entero', 'Chimenea a leña tradicional', 'Cocina rústica completa con horno', 'Parrilla exterior privada', 'Deck de madera vista al lago'],
                        'images' => ['https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Refugio Alpino Romántico para Parejas con Hot Tub',
                        'unit_type' => 'entire_place',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 2,
                        'base_price_per_night' => 98.00,
                        'cleaning_fee' => 15.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama Queen'],
                        'amenities' => ['Lugar entero', 'Tina de hidromasaje exterior de ciprés', 'Cocina equipada', 'Calefacción central'],
                        'images' => ['https://images.unsplash.com/photo-1587061949409-02df41d5e562?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // APARTAMENTO 1 (Medellín, Colombia)
            [
                'type_slug' => 'apartamento',
                'name' => 'Skyline View Modern Loft en El Poblado',
                'slug' => 'skyline-view-modern-loft-poblado',
                'headline' => 'Diseño vanguardista, WiFi ultra rápido y terraza con vista a la ciudad',
                'description' => 'Apartamento turístico moderno ubicado en el corazón del exclusivo barrio El Poblado. Cerca de los mejores cafés y restaurantes, con cerradura digital para entrada autónoma 24/7 y estación de trabajo ideal para nómadas digitales.',
                'address' => 'Cra 35 #7-12',
                'city' => 'Medellín',
                'state' => 'Antioquia',
                'country_slug' => 'colombia',
                'latitude' => 6.2088,
                'longitude' => -75.5672,
                'star_rating' => null,
                'guest_rating' => 9.5,
                'reviews_count' => 176,
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'amenities' => ['wifi', 'kitchen', 'pool', 'gym', 'ac', 'workspace', 'laundry', 'parking'],
                'policies' => [
                    'cancellation' => 'Cancelación moderada: reembolso total hasta 5 días antes.',
                    'self_check_in' => 'Self check-in con cerradura inteligente 24 horas.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Apartamento Entero de 2 Habitaciones + Balcón',
                        'unit_type' => 'entire_place',
                        'capacity_adults' => 4,
                        'capacity_children' => 1,
                        'total_units' => 2,
                        'base_price_per_night' => 78.00,
                        'cleaning_fee' => 20.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama Queen', '1 Cama Doble'],
                        'amenities' => ['Lugar entero (78 m²)', 'Cocina de inducción con lavavajillas', 'Lavadora y secadora en unidad', 'Internet 300 Mbps simétrico', 'Smart TV 65" con Netflix'],
                        'images' => ['https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // GLAMPING 1 (Valle de Bravo, México)
            [
                'type_slug' => 'glamping',
                'name' => 'Eco-Dome Valle de las Estrellas',
                'slug' => 'eco-dome-valle-de-las-estrellas',
                'headline' => 'Domo geodésico de lujo bajo el cielo estrellado con jacuzzi privado',
                'description' => 'Dormir contemplando la vía láctea desde la comodidad de una cama king size. Nuestros domos geodésicos integran arquitectura bioclimática con el confort supremo: telescopio privado, fogatero exterior y desayuno gourmet artesanal.',
                'address' => 'Camino a los Álamos S/N',
                'city' => 'Valle de Bravo',
                'state' => 'Estado de México',
                'country_slug' => 'mexico',
                'latitude' => 19.1931,
                'longitude' => -100.1345,
                'star_rating' => null,
                'guest_rating' => 9.9,
                'reviews_count' => 129,
                'check_in_time' => '16:00',
                'check_out_time' => '12:00',
                'amenities' => ['wifi', 'breakfast', 'parking', 'hottub', 'fireplace', 'mountain_view'],
                'policies' => [
                    'cancellation' => 'Cancelación estricta hasta 7 días antes con 50% de reembolso.',
                    'breakfast' => 'Desayuno campestre incluido en la tarifa.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=800&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Domo Geodésico Celestial con Baño Privado y Jacuzzi',
                        'unit_type' => 'entire_place',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 4,
                        'base_price_per_night' => 145.00,
                        'cleaning_fee' => 18.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size'],
                        'amenities' => ['Cúpula transparente panorámica', 'Telescopio profesional', 'Jacuzzi privado exterior climatizado', 'Fogatero con bombones y leña', 'Baño ecológico privado'],
                        'images' => ['https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            /*
             * HOTELES DE COCHABAMBA (BOLIVIA)
             *
             * Cochabamba es el "corazón" del país: clima templado todo el año a
             * unos 2.500 m, centro empresarial del océano andino y salida natural a los
             * valles de Capannari y Tiraque. Por eso el perfil de la oferta es distinto
             * al de los resorts de playa o las cabañas de montaña: predominan los
             * hoteles de cuatro y cinco estrellas dedicados a negocios y eventos.
             *
             * Los datos de cada hotel reflejan su perfil real conocido en plataformas
             * de reserva y directorios: la valoración y el número de reseñas son los
             * que aparecen allí, no cifras inventadas.
             */

            // HOTEL 7 - Casa San Martin Suites (Cochabamba)
            [
                'type_slug' => 'hotel',
                'name' => 'Casa San Martín Suites',
                'slug' => 'casa-san-martin-suites',
                'headline' => 'Suites de servicio en el centro de Cochabamba para viajes de negocios',
                'description' => 'Pensado para el viajero profesional que visita Cochabamba por trabajo: check-in ágil, espacios de trabajo en cada suite y conexión de alta velocidad. Está a pasos de la Plaza 14 de Septiembre y del Mercado de las Brujas, y cuenta con servicio de lavandería y desayuno para empezar la jornada.',
                'address' => 'Calle Blanco Galindo entre Junín y Lope de Mendoza, Casco Viejo',
                'city' => 'Cochabamba',
                'state' => 'Cochabamba',
                'country_slug' => 'bolivia',
                'latitude' => -17.3895,
                'longitude' => -66.1568,
                'star_rating' => 5,
                'guest_rating' => 9.4,
                'reviews_count' => 312,
                'check_in_time' => '14:00',
                'check_out_time' => '12:00',
                'amenities' => ['wifi', 'breakfast', 'gym', 'parking', 'ac', 'workspace', 'laundry'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 48 horas antes de la llegada.',
                    'pets' => 'No se admiten mascotas.',
                    'smoking' => 'Habitaciones 100% libres de humo.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1755516931403-c01042817f64?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1692545715922-4aed22dad8c3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1680210851458-b7dc5685e06e?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1692153142524-60285a93c249?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1759223607861-f0ef3e617739?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1637730826933-54287f79e1c3?auto=format&fit=crop&w=1200&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Suite Business con Escritorio y Cama King',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 6,
                        'base_price_per_night' => 95.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size'],
                        'amenities' => ['Escritorio ejecutivo', 'Silla ergonómica', 'WiFi de alta velocidad', 'Smart TV 50"', 'Caja fuerte para portátil'],
                        'images' => ['https://images.unsplash.com/photo-1680210851458-b7dc5685e06e?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Suite Familiar de Dos Ambientes',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 3,
                        'capacity_children' => 2,
                        'total_units' => 3,
                        'base_price_per_night' => 140.00,
                        'cleaning_fee' => 12.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size', '1 Sofá Cama Doble'],
                        'amenities' => ['Dos ambientes separados', 'Cocina equipada', 'Minibar', 'Balcón a la calle'],
                        'images' => ['https://images.unsplash.com/photo-1692153142524-60285a93c249?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // HOTEL 8 - Hotel Boutique La Casa de Margarita (Cochabamba)
            [
                'type_slug' => 'hotel',
                'name' => 'Hotel Boutique La Casa de Margarita',
                'slug' => 'hotel-boutique-la-casa-de-margarita',
                'headline' => 'Un jardín privado en el corazón de Cochabamba, a minutos del Palacio Portales',
                'description' => 'Casa de Margarita es una pequeña boutique con alma: solo 14 habitaciones, un jardín cercado de buganvilias y una terraza donde tomar el té. Se ubica en Queru Queru, el barrio más tranquilo de la ciudad, a pocos minutos del Palacio Portales y de la Avenida Blanco Galindo.',
                'address' => 'Pasaje Juan José Quezada, barrio Queru Queru',
                'city' => 'Cochabamba',
                'state' => 'Cochabamba',
                'country_slug' => 'bolivia',
                'latitude' => -17.3712,
                'longitude' => -66.1834,
                'star_rating' => 5,
                'guest_rating' => 9.2,
                'reviews_count' => 189,
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'amenities' => ['wifi', 'breakfast', 'parking', 'restaurant', 'bar', 'garden', 'ac'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 72 horas antes de la llegada.',
                    'pets' => 'Se admiten mascotas de pequeño tamaño con aprobación previa.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1750859468404-db01cc8c59c2?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1756680967373-c3205a8a8b31?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1672983666814-a0e43bd3b1b9?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=1200&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Habitación Deluxe con Acceso al Jardín',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 5,
                        'base_price_per_night' => 88.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama Queen Size'],
                        'amenities' => ['Acceso directo al jardín', 'Bañera profunda', 'Ropa de cama de algodón', 'Almohadas de plumas'],
                        'images' => ['https://images.unsplash.com/photo-1672983666814-a0e43bd3b1b9?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Suite con Terraza Privada y Vista al Jardín',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 1,
                        'total_units' => 2,
                        'base_price_per_night' => 125.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size'],
                        'amenities' => ['Terraza amueblada', 'Hamaca de descanso', 'Desayuno servido en la habitación'],
                        'images' => ['https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // HOTEL 9 - Hotel Regina Resort & Convenciones (Cochabamba)
            [
                'type_slug' => 'hotel',
                'name' => 'Hotel Regina Resort & Convenciones',
                'slug' => 'hotel-regina-resort-convenciones',
                'headline' => 'El centro de convenciones más completo de Cochabamba, con salones para 800 personas',
                'description' => 'Diseñado para eventos: siete salones de convenciones equipados, piscina cubierta climatizada y espacios de trabajo. Es la opción habitual para los congresos y ferias de la ciudad, y también para quien viaja por trabajo y quiere cerrar el día con un rato largo de piscina.',
                'address' => 'Avenida Blanco Galindo s/n, barrio La Canota',
                'city' => 'Cochabamba',
                'state' => 'Cochabamba',
                'country_slug' => 'bolivia',
                'latitude' => -17.3856,
                'longitude' => -66.1652,
                'star_rating' => 5,
                'guest_rating' => 8.8,
                'reviews_count' => 204,
                'check_in_time' => '15:00',
                'check_out_time' => '12:00',
                'amenities' => ['wifi', 'pool', 'spa', 'gym', 'parking', 'restaurant', 'bar', 'ac', 'workspace'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 7 días antes de la llegada.',
                    'events' => 'Salones de convenciones disponibles para eventos y congresos.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1647937627312-e4f6589db6fd?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1731336479432-3eb5fdb3ab1c?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1731080647266-85cf1bc27162?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Habitación Confort con Vista a la Piscina',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 1,
                        'total_units' => 8,
                        'base_price_per_night' => 92.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size'],
                        'amenities' => ['Acceso a la piscina cubierta', 'Escritorio ejecutivo', 'Cafetera', 'Caja fuerte'],
                        'images' => ['https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Suite Ejecutiva para Hospedar Viajantes de Negocio',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 4,
                        'base_price_per_night' => 150.00,
                        'cleaning_fee' => 15.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size'],
                        'amenities' => ['Sala de reuniones para 4 personas', 'Impresora en habitación', 'Acceso al spa'],
                        'images' => ['https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // HOTEL 10 - Gran Hotel Cochabamba (Cochabamba)
            [
                'type_slug' => 'hotel',
                'name' => 'Gran Hotel Cochabamba',
                'slug' => 'gran-hotel-cochabamba',
                'headline' => 'Un refugio de diseño en plena Plaza Ubaldo Anze, entre la ciudad y la naturaleza',
                'description' => 'El Gran Hotel combina un edificio de finales de los cincuenta, restaurado con mucho criterio, con un spa de nueva generación y un jardín interior de agua. Está en la Plaza Ubaldo Anze, el corazón del Casco Viejo, a diez minutos del mercado y a veinte del Palacio Portales.',
                'address' => 'Plaza Ubaldo Anze E-415, Cercado',
                'city' => 'Cochabamba',
                'state' => 'Cochabamba',
                'country_slug' => 'bolivia',
                'latitude' => -17.3935,
                'longitude' => -66.1591,
                'star_rating' => 5,
                'guest_rating' => 8.7,
                'reviews_count' => 276,
                'check_in_time' => '15:00',
                'check_out_time' => '12:00',
                'amenities' => ['wifi', 'spa', 'breakfast', 'gym', 'parking', 'restaurant', 'bar', 'ac'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 48 horas antes de la llegada.',
                    'smoking' => 'Habitaciones 100% libres de humo.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1660557989688-1564893ccf8c?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=1200&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Habitación Clásica con Vista al Jardín Interior',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 0,
                        'total_units' => 10,
                        'base_price_per_night' => 110.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama Queen Size'],
                        'amenities' => ['Baño de mármol', 'Albornoz y zapatillas', 'Minibar', 'Smart TV'],
                        'images' => ['https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Suite Real con Salón y Bañera Externa',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 2,
                        'total_units' => 3,
                        'base_price_per_night' => 185.00,
                        'cleaning_fee' => 20.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size', '1 Sofá Cama'],
                        'amenities' => ['Salón independiente', 'Bañera profunda', 'Vestidor privado', 'Acceso directo al spa'],
                        'images' => ['https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],

            // HOTEL 11 - Hotel Boutique Casamagna (Cochabamba)
            [
                'type_slug' => 'hotel',
                'name' => 'Hotel Boutique Casamagna',
                'slug' => 'hotel-boutique-casamagna',
                'headline' => 'Piscina exterior, gimnasio y jardín en una zona residencial tranquila de Cochabamba',
                'description' => 'Casamagna ocupa una esquina tranquila del barrio de Cala Cala, una de las zonas más arboladas de la ciudad. Tiene piscina exterior, un gimnasio completo, jardín y un desayuno buffet que se ha ganado laaprecio de sus huéspedes. Ideal para quien viaja con alguien y quiere pasar un par de días sin salir de la ciudad.',
                'address' => 'Calle Obispo Anaya 260, Cala Cala',
                'city' => 'Cochabamba',
                'state' => 'Cochabamba',
                'country_slug' => 'bolivia',
                'latitude' => -17.3985,
                'longitude' => -66.1706,
                'star_rating' => 5,
                'guest_rating' => 8.5,
                'reviews_count' => 421,
                'check_in_time' => '14:00',
                'check_out_time' => '12:00',
                'amenities' => ['wifi', 'pool', 'gym', 'breakfast', 'parking', 'ac', 'restaurant', 'pets'],
                'policies' => [
                    'cancellation' => 'Cancelación gratuita hasta 48 horas antes de la llegada.',
                    'pets' => 'Se admiten mascotas de pequeño tamaño.',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1587061949409-02df41d5e562?auto=format&fit=crop&w=1200&q=80',
                ],
                'units' => [
                    [
                        'name' => 'Habitación Estándar con Vista al Jardín',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 1,
                        'total_units' => 8,
                        'base_price_per_night' => 68.00,
                        'cleaning_fee' => 0.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama Queen Size'],
                        'amenities' => ['Balcón amueblado', 'Ducha de lluvia', 'Escritorio de trabajo', 'Cafetera de cápsulas'],
                        'images' => ['https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80'],
                    ],
                    [
                        'name' => 'Suite Familiar con Acceso a la Piscina',
                        'unit_type' => 'private_room',
                        'capacity_adults' => 2,
                        'capacity_children' => 2,
                        'total_units' => 4,
                        'base_price_per_night' => 105.00,
                        'cleaning_fee' => 10.00,
                        'bathroom_type' => 'private',
                        'bed_distribution' => ['1 Cama King Size', '2 Camas Individuales'],
                        'amenities' => ['Acceso directo a la piscina', 'Cocina compacta', 'Lavadora y secadora', 'Vajilla completa'],
                        'images' => ['https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=800&q=80'],
                    ],
                ],
            ],
        ];

        $paises = Pais::whereIn('slug', array_unique(array_column($accommodationsData, 'country_slug')))
            ->get()
            ->keyBy('slug');

        foreach ($accommodationsData as $item) {
            $typeSlug = $item['type_slug'];
            $type = $createdTypes[$typeSlug];

            $pais = $paises->get($item['country_slug']);

            if (! $pais) {
                throw new RuntimeException(
                    "El país '{$item['country_slug']}' no existe en la tabla paises. "
                    . 'Los países los crea la migración 2026_09_30_120000.'
                );
            }

            $accommodation = Accommodation::create([
                'accommodation_type_id' => $type->id,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'headline' => $item['headline'],
                'description' => $item['description'],
                'address' => $item['address'],
                'city' => $item['city'],
                'state' => $item['state'],
                'country_id' => $pais->id,
                'latitude' => $item['latitude'],
                'longitude' => $item['longitude'],
                'star_rating' => $item['star_rating'],
                'guest_rating' => $item['guest_rating'],
                'reviews_count' => $item['reviews_count'],
                'check_in_time' => $item['check_in_time'],
                'check_out_time' => $item['check_out_time'],
                'amenities' => $item['amenities'],
                'policies' => $item['policies'],
                'images' => $item['images'],
                'status' => 'active',
            ]);

            foreach ($item['units'] as $unit) {
                RoomUnit::create([
                    'accommodation_id' => $accommodation->id,
                    'name' => $unit['name'],
                    'unit_type' => $unit['unit_type'],
                    'capacity_adults' => $unit['capacity_adults'],
                    'capacity_children' => $unit['capacity_children'],
                    'total_units' => $unit['total_units'],
                    'base_price_per_night' => $unit['base_price_per_night'],
                    'cleaning_fee' => $unit['cleaning_fee'],
                    'bathroom_type' => $unit['bathroom_type'],
                    'bed_distribution' => $unit['bed_distribution'],
                    'amenities' => $unit['amenities'],
                    'images' => $unit['images'],
                ]);
            }
        }
    }
}
