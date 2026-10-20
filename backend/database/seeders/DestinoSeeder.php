<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Pais;
use Illuminate\Database\Seeder;

/**
 * Catálogo de destinos turísticos.
 *
 * Los destinos de Bolivia están primero porque es el país que motiva este
 * catálogo; después van los del resto de países publicados. Cada destino apunta
 * a una ciudad real del país (`ciudad`), que es lo que permite que su ficha
 * liste los alojamientos publicados allí. Los que son parajes naturales (el
 * Salar de Uyuni, Iguazú) llevan igualmente su ciudad de referencia.
 *
 * Las imágenes son de demostración, como en el resto de seeders: se reutilizan
 * fotos de Unsplash que ya usan los alojamientos para no depender de enlaces
 * que puedan desaparecer.
 */
class DestinoSeeder extends Seeder
{
    /** Identificadores de fotos de Unsplash ya verificados en este proyecto. */
    private const IMAGENES = [
        'photo-1510312305653-8ed496efae75',
        'photo-1510798831971-661eb04b3739',
        'photo-1542314831-068cd1dbfeeb',
        'photo-1499696010180-025ef6e1a8f9',
        'photo-1507652313519-d4e9174996dd',
        'photo-1520250497591-112f2f40a3f4',
        'photo-1540555700478-4be289fbecef',
        'photo-1571003123894-1f0594d2b5d9',
        'photo-1566073771259-6a8506099945',
        'photo-1529290130-4ca3753253ae',
    ];

    /**
     * Destinos por slug de país. `img` indexa en IMAGENES para no repetir URLs.
     */
    private function destinos(): array
    {
        return [
            // -------------------------------------------------------------
            // Bolivia
            // -------------------------------------------------------------
            'bolivia' => [
                [
                    'name' => 'Salar de Uyuni',
                    'slug' => 'salar-de-uyuni',
                    'city' => 'Uyuni',
                    'img' => 3,
                    'lat' => -20.1338, 'lng' => -67.4891,
                    'featured' => true, 'order' => 1,
                    'es' => 'El desierto de sal más grande del mundo, con 10.582 km² de horizonte blanco que se convierte en espejo durante la época de lluvias. Alrededor esperan el cementerio de trenes, las lagunas de colores y los geiseres del Siloli.',
                    'en' => 'The largest salt flat on Earth, 10,582 km² of white horizon that turns into a mirror during the rainy season. Around it lie the train cemetery, the coloured lagoons and the geysers of Siloli.',
                ],
                [
                    'name' => 'La Paz',
                    'slug' => 'la-paz',
                    'city' => 'La Paz',
                    'img' => 5,
                    'lat' => -16.5000, 'lng' => -68.1500,
                    'featured' => true, 'order' => 2,
                    'es' => 'La sede de gobierno más alta del mundo, encajada en un cañón andino. Su teleférico urbano, los mercados de la calle Sagárnaga y el mirador de Killi Killi son la mejor forma de entender la ciudad.',
                    'en' => "The world's highest seat of government, wedged into an Andean canyon. Its urban cable car, the markets of Sagárnaga street and the Killi Killi viewpoint are the best way to understand the city.",
                ],
                [
                    'name' => 'Sucre',
                    'slug' => 'sucre',
                    'city' => 'Sucre',
                    'img' => 9,
                    'lat' => -19.0333, 'lng' => -65.2627,
                    'featured' => false, 'order' => 3,
                    'es' => 'La capital histórica de Bolivia, declarada Patrimonio de la Humanidad: casas encaladas, balcones de madera y la Plaza 25 de Mayo. Es una ciudad universitaria, tranquila y de clima privilegiado.',
                    'en' => 'Bolivia\'s historic capital, a UNESCO World Heritage Site: whitewashed houses, wooden balconies and Plaza 25 de Mayo. A calm, student city with a privileged climate.',
                ],
                [
                    'name' => 'Potosí',
                    'slug' => 'potosi',
                    'city' => 'Potosí',
                    'img' => 2,
                    'lat' => -19.5836, 'lng' => -65.7531,
                    'featured' => false, 'order' => 4,
                    'es' => 'La ciudad que se construyó sobre la plata del Cerro Rico, a más de 4.000 m de altitud. Su Casa de la Moneda y el recorrido por las minas cuentan la historia que financió un imperio.',
                    'en' => 'The city built on the silver of Cerro Rico, above 4,000 m of altitude. Its Mint and the mine tours tell the story that financed an empire.',
                ],
                [
                    'name' => 'Copacabana y el Titicaca',
                    'slug' => 'copacabana-titicaca',
                    'city' => 'Copacabana',
                    'img' => 4,
                    'lat' => -16.1667, 'lng' => -69.0833,
                    'featured' => false, 'order' => 5,
                    'es' => 'A orillas del lago navegable más alto del mundo, Copacabana es la puerta a la Isla del Sol y su red de senderos incas. La basílica y la virgen morena atraen peregrinos de todo el país.',
                    'en' => "On the shore of the world's highest navigable lake, Copacabana is the gateway to the Island of the Sun and its network of Inca trails. The basilica and its dark-skinned Virgin draw pilgrims from across the country.",
                ],
                [
                    'name' => 'Santa Cruz de la Sierra',
                    'slug' => 'santa-cruz-de-la-sierra',
                    'city' => 'Santa Cruz de la Sierra',
                    'img' => 0,
                    'lat' => -17.7833, 'lng' => -63.1821,
                    'featured' => false, 'order' => 6,
                    'es' => 'La ciudad más grande y cálida de Bolivia, motor económico del oriente. Es la base para explorar los Valles Cruceños, las misiones jesuíticas de Chiquitos y el pantanal boliviano.',
                    'en' => 'Bolivia\'s largest and warmest city, the economic engine of the east. It is the base for exploring the Cruceño valleys, the Jesuit missions of Chiquitos and the Bolivian Pantanal.',
                ],
                [
                    'name' => 'Tarija',
                    'slug' => 'tarija',
                    'city' => 'Tarija',
                    'img' => 1,
                    'lat' => -21.5355, 'lng' => -64.7296,
                    'featured' => false, 'order' => 7,
                    'es' => 'Capital del vino y del singani bolivianos, con un microclima de valle que recuerda al sur de España. Sus bodegas del valle de la Concepción y los cañones de la Angostura son parada obligada.',
                    'en' => 'The capital of Bolivian wine and singani, with a valley microclimate that recalls southern Spain. Its wineries in the Concepción valley and the Angostura canyons are a must.',
                ],
                [
                    'name' => 'Cochabamba',
                    'slug' => 'cochabamba',
                    'city' => 'Cochabamba',
                    'img' => 8,
                    'lat' => -17.3895, 'lng' => -66.1568,
                    'featured' => true, 'order' => 8,
                    'es' => 'El valle templado que llaman el corazón de Bolivia. Ciudad de mercados y gastronomía, con el Cristo de la Concordia en lo alto, y punto de partida hacia los paisajes de la cordillera Tunari.',
                    'en' => 'The temperate valley known as the heart of Bolivia. A city of markets and food, crowned by the Cristo de la Concordia, and a starting point for the Tunari mountain range.',
                ],
            ],

            // -------------------------------------------------------------
            // España
            // -------------------------------------------------------------
            'espana' => [
                [
                    'name' => 'Madrid',
                    'slug' => 'madrid',
                    'city' => 'Madrid',
                    'img' => 5,
                    'lat' => 40.4168, 'lng' => -3.7038,
                    'featured' => true, 'order' => 1,
                    'es' => 'La capital reúne el Prado, el Reina Sofía y el Thyssen en un paseo, además de la vida de sus barrios y su cocina de taberna. Desde aquí se llega en media hora a Toledo y Segovia.',
                    'en' => 'The capital brings the Prado, the Reina Sofía and the Thyssen together on one boulevard, along with neighbourhood life and tavern food. Toledo and Segovia are half an hour away.',
                ],
                [
                    'name' => 'Barcelona',
                    'slug' => 'barcelona',
                    'city' => 'Barcelona',
                    'img' => 9,
                    'lat' => 41.3874, 'lng' => 2.1686,
                    'featured' => true, 'order' => 2,
                    'es' => 'Entre el mar y el Tibidabo, la ciudad combina la arquitectura de Gaudí, el laberinto gótico y las playas urbanas. Su oferta de hostales y hoteles de diseño es de las mejores de Europa.',
                    'en' => 'Between the sea and Tibidabo, the city combines Gaudí architecture, the Gothic labyrinth and urban beaches. Its hostels and design hotels are among the best in Europe.',
                ],
                [
                    'name' => 'Sevilla',
                    'slug' => 'sevilla',
                    'city' => 'Sevilla',
                    'img' => 6,
                    'lat' => 37.3891, 'lng' => -5.9845,
                    'featured' => false, 'order' => 3,
                    'es' => 'La capital andaluza, con la Giralda, el Alcázar y el barrio de Santa Cruz. En primavera se llena con la Semana Santa y la Feria de Abril, dos de las fiestas más grandes de España.',
                    'en' => 'The Andalusian capital, with the Giralda, the Alcázar and the Santa Cruz quarter. In spring it fills up for Holy Week and the April Fair, two of the biggest festivals in Spain.',
                ],
                [
                    'name' => 'Valencia',
                    'slug' => 'valencia',
                    'city' => 'Valencia',
                    'img' => 3,
                    'lat' => 39.4699, 'lng' => -0.3763,
                    'featured' => false, 'order' => 4,
                    'es' => 'Cuna de la paella, con la Ciudad de las Artes y las Ciencias como icono contemporáneo. Su casco histórico y el mercado central conviven con kilómetros de playa a pocos minutos.',
                    'en' => 'The birthplace of paella, with the City of Arts and Sciences as its contemporary icon. Its old town and central market sit within minutes of kilometres of beach.',
                ],
                [
                    'name' => 'Granada',
                    'slug' => 'granada',
                    'city' => 'Granada',
                    'img' => 2,
                    'lat' => 37.1773, 'lng' => -3.5986,
                    'featured' => false, 'order' => 5,
                    'es' => 'La Alhambra y el Generalife hacen de Granada una visita obligada, con el Albaicín y el Sacromonte como telón de fondo. La tapa gratuita sigue siendo una institución local.',
                    'en' => 'The Alhambra and the Generalife make Granada unmissable, with the Albaicín and Sacromonte as a backdrop. The free tapa is still a local institution.',
                ],
                [
                    'name' => 'San Sebastián',
                    'slug' => 'san-sebastian',
                    'city' => 'San Sebastián',
                    'img' => 4,
                    'lat' => 43.3183, 'lng' => -1.9812,
                    'featured' => false, 'order' => 6,
                    'es' => 'La bahía de La Concha y la playa de Zurriola enmarcan la capital gastronómica del País Vasco. Sus pintxos y sus restaurantes con estrellas la han hecho famosa en todo el mundo.',
                    'en' => 'La Concha bay and Zurriola beach frame the culinary capital of the Basque Country. Its pintxos and starred restaurants have made it famous around the world.',
                ],
                [
                    'name' => 'Santiago de Compostela',
                    'slug' => 'santiago-de-compostela',
                    'city' => 'Santiago de Compostela',
                    'img' => 1,
                    'lat' => 42.8782, 'lng' => -8.5448,
                    'featured' => false, 'order' => 7,
                    'es' => 'El final del Camino, con la catedral del Apóstol como meta de peregrinos de todo el mundo. Su casco de piedra, declarado Patrimonio de la Humanidad, se recorre bajo la lluvia y a gusto.',
                    'en' => "The end of the Way, with the Apostle's cathedral as the goal for pilgrims worldwide. Its stone old town, a World Heritage Site, is best explored in the rain and with pleasure.",
                ],
                [
                    'name' => 'Toledo',
                    'slug' => 'toledo',
                    'city' => 'Toledo',
                    'img' => 0,
                    'lat' => 39.8628, 'lng' => -4.0273,
                    'featured' => false, 'order' => 8,
                    'es' => 'La ciudad de las tres culturas, encaramada sobre el Tajo, conserva sinagogas, mezquitas e iglesias a pocos metros. Su casco medieval es uno de los mejor conservados de Europa.',
                    'en' => 'The city of three cultures, perched above the Tagus, keeps synagogues, mosques and churches within metres of one another. Its medieval old town is one of the best preserved in Europe.',
                ],
            ],

            // -------------------------------------------------------------
            // México
            // -------------------------------------------------------------
            'mexico' => [
                [
                    'name' => 'Ciudad de México',
                    'slug' => 'ciudad-de-mexico',
                    'city' => 'Ciudad de México',
                    'img' => 9,
                    'lat' => 19.4326, 'lng' => -99.1332,
                    'featured' => true, 'order' => 1,
                    'es' => 'La capital del país reúne el Zócalo, Teotihuacán a las afueras y los murales de Diego Rivera. Sus barrios como Coyoacán y Roma condensan gastronomía, arte y noche.',
                    'en' => 'The country\'s capital brings together the Zócalo, Teotihuacán on its outskirts and Diego Rivera\'s murals. Neighbourhoods such as Coyoacán and Roma pack in food, art and nightlife.',
                ],
                [
                    'name' => 'Cancún',
                    'slug' => 'cancun',
                    'city' => 'Cancún',
                    'img' => 3,
                    'lat' => 21.1619, 'lng' => -86.8515,
                    'featured' => true, 'order' => 2,
                    'es' => 'La puerta del Caribe mexicano: playas de arena blanca, arrecifes frente a la costa y cenotes en el interior. De aquí parte la ruta hacia Isla Mujeres y la Riviera Maya.',
                    'en' => 'The gateway to the Mexican Caribbean: white-sand beaches, offshore reefs and cenotes inland. The route to Isla Mujeres and the Riviera Maya starts here.',
                ],
                [
                    'name' => 'Tulum',
                    'slug' => 'tulum',
                    'city' => 'Tulum',
                    'img' => 4,
                    'lat' => 20.2114, 'lng' => -87.4654,
                    'featured' => false, 'order' => 3,
                    'es' => 'Las ruinas mayas frente al mar son el emblema de la Riviera Maya. Hoy combina hoteles de diseño, cenotes y una reserva de biosfera que se explora en bicicleta.',
                    'en' => 'The Maya ruins facing the sea are the emblem of the Riviera Maya. Today it combines design hotels, cenotes and a biosphere reserve best explored by bicycle.',
                ],
                [
                    'name' => 'Oaxaca',
                    'slug' => 'oaxaca',
                    'city' => 'Oaxaca',
                    'img' => 1,
                    'lat' => 17.0732, 'lng' => -96.7266,
                    'featured' => false, 'order' => 4,
                    'es' => 'Capital gastronómica de México, con el mole, el mezcal y el mercado Benito Juárez como protagonistas. Monte Albán y los talleres de artesanos completan la visita.',
                    'en' => "Mexico's culinary capital, with mole, mezcal and Benito Juárez market as the stars. Monte Albán and the artisan workshops complete the visit.",
                ],
                [
                    'name' => 'Guanajuato',
                    'slug' => 'guanajuato',
                    'city' => 'Guanajuato',
                    'img' => 0,
                    'lat' => 21.0190, 'lng' => -101.2574,
                    'featured' => false, 'order' => 5,
                    'es' => 'Ciudad minera Patrimonio de la Humanidad, con callejones imposibles y callejones cubiertos por el que se sumerge la ciudad. El Festival Cervantino la llena cada octubre.',
                    'en' => 'A mining city and World Heritage Site, with impossible alleys and underground streets the city dives into. The Cervantino Festival fills it every October.',
                ],
                [
                    'name' => 'San Miguel de Allende',
                    'slug' => 'san-miguel-de-allende',
                    'city' => 'San Miguel de Allende',
                    'img' => 6,
                    'lat' => 20.9144, 'lng' => -100.7433,
                    'featured' => false, 'order' => 6,
                    'es' => 'Su iglesia rosa de estilo neogótico se ve desde todo el valle. Es refugio de artistas y jubilados extranjeros, con galerías, terrazas y una escena gastronómica notable.',
                    'en' => 'Its pink neo-Gothic church can be seen from across the valley. A refuge for artists and foreign retirees, with galleries, terraces and a notable food scene.',
                ],
                [
                    'name' => 'Puerto Vallarta',
                    'slug' => 'puerto-vallarta',
                    'city' => 'Puerto Vallarta',
                    'img' => 7,
                    'lat' => 20.6534, 'lng' => -105.2253,
                    'featured' => false, 'order' => 7,
                    'es' => 'Entre la Sierra Madre y el Pacífico, su malecón y su Zona Romántica son famosos por las puestas de sol. Es la base para ver ballenas jorobadas de diciembre a marzo.',
                    'en' => 'Between the Sierra Madre and the Pacific, its boardwalk and Romantic Zone are famous for sunsets. It is the base for spotting humpback whales from December to March.',
                ],
                [
                    'name' => 'Valle de Bravo',
                    'slug' => 'valle-de-bravo',
                    'city' => 'Valle de Bravo',
                    'img' => 2,
                    'lat' => 19.1931, 'lng' => -100.1345,
                    'featured' => false, 'order' => 8,
                    'es' => 'Pueblo Mágico de casas encaladas junto al lago y rodeado de pinos. Es el destino de fin de semana de la Ciudad de México y capital del parapente en el país.',
                    'en' => 'A Magical Town of whitewashed houses by the lake and ringed by pines. It is Mexico City\'s weekend getaway and the country\'s paragliding capital.',
                ],
            ],

            // -------------------------------------------------------------
            // Argentina
            // -------------------------------------------------------------
            'argentina' => [
                [
                    'name' => 'Buenos Aires',
                    'slug' => 'buenos-aires',
                    'city' => 'Buenos Aires',
                    'img' => 9,
                    'lat' => -34.6037, 'lng' => -58.3816,
                    'featured' => true, 'order' => 1,
                    'es' => 'La capital porteña mezcla el tango de San Telmo con la arquitectura de Recoleta y la vida de Palermo. Sus librerías, cafés y parrillas justifican una estancia larga.',
                    'en' => 'The porteño capital mixes the tango of San Telmo with Recoleta architecture and the life of Palermo. Its bookshops, cafés and steakhouses justify a long stay.',
                ],
                [
                    'name' => 'Bariloche',
                    'slug' => 'bariloche',
                    'city' => 'Bariloche',
                    'img' => 1,
                    'lat' => -41.1335, 'lng' => -71.3103,
                    'featured' => true, 'order' => 2,
                    'es' => 'A orillas del Nahuel Huapi, es la capital de los lagos patagónicos: bosques, nieve en invierno y chocolates en el centro. El Circuito Chico es la ruta clásica.',
                    'en' => 'On the shore of Nahuel Huapi, it is the capital of the Patagonian lakes: forests, winter snow and chocolate downtown. The Circuito Chico is the classic drive.',
                ],
                [
                    'name' => 'Mendoza',
                    'slug' => 'mendoza',
                    'city' => 'Mendoza',
                    'img' => 5,
                    'lat' => -32.8895, 'lng' => -68.8458,
                    'featured' => false, 'order' => 3,
                    'es' => 'La tierra del Malbec, con bodegas al pie de los Andes y el Aconcagua como techo de América. Las rutas del vino de Maipú y Luján de Cuyo son su gran atractivo.',
                    'en' => 'The land of Malbec, with wineries at the foot of the Andes and Aconcagua, the highest peak in the Americas. The wine routes of Maipú and Luján de Cuyo are its great draw.',
                ],
                [
                    'name' => 'Salta',
                    'slug' => 'salta',
                    'city' => 'Salta',
                    'img' => 0,
                    'lat' => -24.7821, 'lng' => -65.4232,
                    'featured' => false, 'order' => 4,
                    'es' => 'La linda, como la llaman, conserva su catedral y su cabildo rosados. Es la base para la Quebrada de Humahuaca y los Valles Calchaquíes, de paisajes imposibles.',
                    'en' => 'Known as "la linda", it keeps its pink cathedral and cabildo. It is the base for the Quebrada de Humahuaca and the Calchaquí valleys, with impossible scenery.',
                ],
                [
                    'name' => 'Córdoba',
                    'slug' => 'cordoba',
                    'city' => 'Córdoba',
                    'img' => 6,
                    'lat' => -31.4201, 'lng' => -64.1888,
                    'featured' => false, 'order' => 5,
                    'es' => 'Segunda ciudad del país y sede de la primera universidad argentina. Sus sierras, la Manzana Jesuítica y los valles cercanos la convierten en escapada de fin de semana.',
                    'en' => 'The country\'s second city and home to Argentina\'s first university. Its hills, the Jesuit Block and nearby valleys make it a weekend escape.',
                ],
                [
                    'name' => 'Ushuaia',
                    'slug' => 'ushuaia',
                    'city' => 'Ushuaia',
                    'img' => 2,
                    'lat' => -54.8019, 'lng' => -68.3030,
                    'featured' => false, 'order' => 6,
                    'es' => 'La ciudad del fin del mundo, puerta del canal Beagle y de los viajes a la Antártida. El parque nacional Tierra del Fuego y el tren del fin del mundo la rodean.',
                    'en' => 'The city at the end of the world, gateway to the Beagle Channel and to Antarctic voyages. Tierra del Fuego National Park and the End of the World Train surround it.',
                ],
                [
                    'name' => 'El Calafate',
                    'slug' => 'el-calafate',
                    'city' => 'El Calafate',
                    'img' => 4,
                    'lat' => -50.3379, 'lng' => -72.2648,
                    'featured' => false, 'order' => 7,
                    'es' => 'La base para visitar el glaciar Perito Moreno, que se desprende en el lago Argentino. El pueblo vive de la estepa y de los turistas que llegan a ver el hielo.',
                    'en' => 'The base for visiting the Perito Moreno glacier, which calves into Lake Argentino. The town lives off the steppe and the travellers who come to see the ice.',
                ],
                [
                    'name' => 'Puerto Iguazú',
                    'slug' => 'puerto-iguazu',
                    'city' => 'Puerto Iguazú',
                    'img' => 7,
                    'lat' => -25.5991, 'lng' => -54.5736,
                    'featured' => false, 'order' => 8,
                    'es' => 'La puerta argentina a las Cataratas del Iguazú, una de las grandes maravillas naturales del mundo. La Garganta del Diablo se recorre por pasarelas sobre el agua.',
                    'en' => 'The Argentine gateway to Iguazú Falls, one of the world\'s great natural wonders. The Devil\'s Throat is explored on walkways over the water.',
                ],
            ],

            // -------------------------------------------------------------
            // Colombia
            // -------------------------------------------------------------
            'colombia' => [
                [
                    'name' => 'Cartagena de Indias',
                    'slug' => 'cartagena-de-indias',
                    'city' => 'Cartagena',
                    'img' => 6,
                    'lat' => 10.3910, 'lng' => -75.4794,
                    'featured' => true, 'order' => 1,
                    'es' => 'La ciudad amurallada, Patrimonio de la Humanidad, con balcones de buganvillas y plazas de piedra. Frente a ella, las islas del Rosario y Barú para el día de playa.',
                    'en' => 'The walled city, a World Heritage Site, with bougainvillea balconies and stone squares. Opposite it, the Rosario and Barú islands for a beach day.',
                ],
                [
                    'name' => 'Bogotá',
                    'slug' => 'bogota',
                    'city' => 'Bogotá',
                    'img' => 9,
                    'lat' => 4.7110, 'lng' => -74.0721,
                    'featured' => false, 'order' => 2,
                    'es' => 'A 2.640 m de altitud, la capital combina La Candelaria, el Museo del Oro y la escena gastronómica de Zona G y Usaquén. Monserrate domina la ciudad desde arriba.',
                    'en' => 'At 2,640 m of altitude, the capital combines La Candelaria, the Gold Museum and the food scene of Zona G and Usaquén. Monserrate towers over the city.',
                ],
                [
                    'name' => 'Medellín',
                    'slug' => 'medellin',
                    'city' => 'Medellín',
                    'img' => 5,
                    'lat' => 6.2442, 'lng' => -75.5812,
                    'featured' => true, 'order' => 3,
                    'es' => 'La ciudad de la eterna primavera, transformada por el metrocable y la cultura paisa. El Poblado y Laureles concentran hoteles, cafés y la mejor vida nocturna del país.',
                    'en' => 'The city of the eternal spring, transformed by the metrocable and paisa culture. El Poblado and Laureles hold the hotels, cafés and the country\'s best nightlife.',
                ],
                [
                    'name' => 'Santa Marta',
                    'slug' => 'santa-marta',
                    'city' => 'Santa Marta',
                    'img' => 4,
                    'lat' => 11.2404, 'lng' => -74.1990,
                    'featured' => false, 'order' => 4,
                    'es' => 'La ciudad más antigua de Colombia, entre el mar Caribe y la Sierra Nevada. Es la puerta al Parque Tayrona, a Minca y a la ciudad perdida de los Tayrona.',
                    'en' => 'Colombia\'s oldest city, between the Caribbean and the Sierra Nevada. It is the gateway to Tayrona Park, Minca and the lost city of the Tayrona.',
                ],
                [
                    'name' => 'Cali',
                    'slug' => 'cali',
                    'city' => 'Cali',
                    'img' => 1,
                    'lat' => 3.4516, 'lng' => -76.5320,
                    'featured' => false, 'order' => 5,
                    'es' => 'La capital mundial de la salsa, con escuelas de baile y callejones que suenan a timba. Su gastronomía vallecaucana y el barrio San Antonio son parada obligada.',
                    'en' => 'The world capital of salsa, with dance schools and alleys that ring with timba. Its Vallecaucana cuisine and the San Antonio quarter are a must.',
                ],
                [
                    'name' => 'San Andrés',
                    'slug' => 'san-andres',
                    'city' => 'San Andrés',
                    'img' => 3,
                    'lat' => 12.5847, 'lng' => -81.7006,
                    'featured' => false, 'order' => 6,
                    'es' => 'La isla del mar de los siete colores, con arrecifes de coral declarados reserva de biosfera. Johnny Cay y el Hoyo Soplador resumen su mezcla de playa y naturaleza.',
                    'en' => 'The island of the seven-colour sea, with coral reefs declared a biosphere reserve. Johnny Cay and the Blowing Hole sum up its mix of beach and nature.',
                ],
                [
                    'name' => 'Villa de Leyva',
                    'slug' => 'villa-de-leyva',
                    'city' => 'Villa de Leyva',
                    'img' => 0,
                    'lat' => 5.6333, 'lng' => -73.5333,
                    'featured' => false, 'order' => 7,
                    'es' => 'Pueblo colonial con la plaza empedrada más grande de Suramérica. Cerca están el desierto de la Candelaria y el fósil de un kronosaurio de 120 millones de años.',
                    'en' => 'A colonial town with the largest cobbled square in South America. Nearby are the Candelaria desert and the fossil of a 120-million-year-old kronosaurus.',
                ],
                [
                    'name' => 'Salento',
                    'slug' => 'salento',
                    'city' => 'Salento',
                    'img' => 7,
                    'lat' => 4.6375, 'lng' => -75.5708,
                    'featured' => false, 'order' => 8,
                    'es' => 'El corazón del Eje Cafetero, con casas de colores y el mirador del Alto de la Cruz. Desde aquí se entra al Valle de Cocora y a sus palmas de cera gigantes.',
                    'en' => 'The heart of the Coffee Axis, with colourful houses and the Alto de la Cruz viewpoint. From here you enter the Cocora Valley and its giant wax palms.',
                ],
            ],
        ];
    }

    public function run(): void
    {
        $destinos = $this->destinos();

        $paises = Pais::whereIn('slug', array_keys($destinos))->get()->keyBy('slug');

        foreach ($destinos as $countrySlug => $lista) {
            $pais = $paises->get($countrySlug);

            if (! $pais) {
                throw new \RuntimeException(
                    "El país '{$countrySlug}' no existe en la tabla paises. "
                    . 'Los países los crean las migraciones 2026_09_30_120000 y 2026_10_02_090000.'
                );
            }

            foreach ($lista as $item) {
                Destination::create([
                    'country_id' => $pais->id,
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'city' => $item['city'],
                    'presentation_text' => [
                        'es' => $item['es'],
                        'en' => $item['en'],
                    ],
                    'image' => $this->imagen($item['img']),
                    'latitude' => $item['lat'],
                    'longitude' => $item['lng'],
                    'featured' => $item['featured'],
                    'order' => $item['order'],
                    'active' => true,
                ]);
            }
        }
    }

    /** URL de una foto de demostración a partir de su índice en IMAGENES. */
    private function imagen(int $indice): string
    {
        $id = self::IMAGENES[$indice % count(self::IMAGENES)];

        return "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=1600&q=80";
    }
}
