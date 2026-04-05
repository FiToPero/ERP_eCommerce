<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorías principales (root)
        $electronics = Category::factory()->create([
            'name' => 'Electrónica',
            'slug' => 'electronica',
            'description' => 'Productos electrónicos',
        ]);

        $clothing = Category::factory()->create([
            'name' => 'Ropa',
            'slug' => 'ropa',
            'description' => 'Ropa y accesorios',
        ]);

        $home = Category::factory()->create([
            'name' => 'Hogar',
            'slug' => 'hogar',
            'description' => 'Productos para el hogar',
        ]);

        $beauty = Category::factory()->create([
            'name' => 'Belleza',
            'slug' => 'belleza',
            'description' => 'Belleza, cosmética y cuidado personal',
        ]);

        $sports = Category::factory()->create([
            'name' => 'Deportes',
            'slug' => 'deportes',
            'description' => 'Artículos deportivos y fitness',
        ]);

        $babies = Category::factory()->create([
            'name' => 'Bebés',
            'slug' => 'bebes',
            'description' => 'Productos para bebés y primera infancia',
        ]);

        $supermarket = Category::factory()->create([
            'name' => 'Supermercado',
            'slug' => 'supermercado',
            'description' => 'Alimentos, bebidas y limpieza',
        ]);

        // Subcategorías de Electrónica
        $smartphones = Category::factory()->create([
            'parent_id' => $electronics->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Teléfonos inteligentes',
        ]);

        $laptops = Category::factory()->create([
            'parent_id' => $electronics->id,
            'name' => 'Laptops',
            'slug' => 'laptops',
            'description' => 'Computadoras portátiles',
        ]);

        $tablets = Category::factory()->create([
            'parent_id' => $electronics->id,
            'name' => 'Tablets',
            'slug' => 'tablets',
            'description' => 'Tabletas',
        ]);

        $audio = Category::factory()->create([
            'parent_id' => $electronics->id,
            'name' => 'Audio',
            'slug' => 'audio',
            'description' => 'Auriculares, parlantes y equipos de sonido',
        ]);

        $tvVideo = Category::factory()->create([
            'parent_id' => $electronics->id,
            'name' => 'TV y Video',
            'slug' => 'tv-y-video',
            'description' => 'Televisores, proyectores y streaming',
        ]);

        $gaming = Category::factory()->create([
            'parent_id' => $electronics->id,
            'name' => 'Gaming',
            'slug' => 'gaming',
            'description' => 'Consolas, accesorios y componentes gamer',
        ]);

        // Sub-subcategorías de Smartphones (3er nivel)
        Category::factory()->create([
            'parent_id' => $smartphones->id,
            'name' => 'iPhone',
            'slug' => 'iphone',
            'description' => 'Smartphones Apple iPhone',
        ]);

        Category::factory()->create([
            'parent_id' => $smartphones->id,
            'name' => 'Samsung Galaxy',
            'slug' => 'samsung-galaxy',
            'description' => 'Smartphones Samsung Galaxy',
        ]);

        Category::factory()->create([
            'parent_id' => $smartphones->id,
            'name' => 'Motorola',
            'slug' => 'motorola',
            'description' => 'Smartphones Motorola',
        ]);

        Category::factory()->create([
            'parent_id' => $smartphones->id,
            'name' => 'Xiaomi',
            'slug' => 'xiaomi',
            'description' => 'Smartphones Xiaomi y Redmi',
        ]);

        Category::factory()->create([
            'parent_id' => $smartphones->id,
            'name' => 'Accesorios para Celulares',
            'slug' => 'accesorios-para-celulares',
            'description' => 'Fundas, cables, cargadores y soportes',
        ]);

        Category::factory()->create([
            'parent_id' => $laptops->id,
            'name' => 'Ultrabooks',
            'slug' => 'ultrabooks',
            'description' => 'Laptops livianas y portátiles de alto rendimiento',
        ]);

        Category::factory()->create([
            'parent_id' => $laptops->id,
            'name' => 'Laptops Gamer',
            'slug' => 'laptops-gamer',
            'description' => 'Portátiles para gaming y alto desempeño gráfico',
        ]);

        Category::factory()->create([
            'parent_id' => $laptops->id,
            'name' => 'Accesorios para Laptops',
            'slug' => 'accesorios-para-laptops',
            'description' => 'Bases, cargadores, mochilas y hubs',
        ]);

        Category::factory()->create([
            'parent_id' => $tablets->id,
            'name' => 'iPad',
            'slug' => 'ipad',
            'description' => 'Tablets Apple iPad',
        ]);

        Category::factory()->create([
            'parent_id' => $tablets->id,
            'name' => 'Tablets Android',
            'slug' => 'tablets-android',
            'description' => 'Tablets Samsung, Lenovo, Xiaomi y otras',
        ]);

        Category::factory()->create([
            'parent_id' => $tablets->id,
            'name' => 'Accesorios para Tablets',
            'slug' => 'accesorios-para-tablets',
            'description' => 'Fundas con teclado, stylus y protectores',
        ]);

        Category::factory()->create([
            'parent_id' => $audio->id,
            'name' => 'Auriculares',
            'slug' => 'auriculares',
            'description' => 'Auriculares inalámbricos, gamers e in-ear',
        ]);

        Category::factory()->create([
            'parent_id' => $audio->id,
            'name' => 'Parlantes',
            'slug' => 'parlantes',
            'description' => 'Parlantes Bluetooth, portátiles y smart speakers',
        ]);

        Category::factory()->create([
            'parent_id' => $audio->id,
            'name' => 'Micrófonos',
            'slug' => 'microfonos',
            'description' => 'Micrófonos para streaming, estudio y podcast',
        ]);

        Category::factory()->create([
            'parent_id' => $tvVideo->id,
            'name' => 'Smart TV',
            'slug' => 'smart-tv',
            'description' => 'Televisores con conectividad y apps integradas',
        ]);

        Category::factory()->create([
            'parent_id' => $tvVideo->id,
            'name' => 'Proyectores',
            'slug' => 'proyectores',
            'description' => 'Proyectores portátiles, hogareños y profesionales',
        ]);

        Category::factory()->create([
            'parent_id' => $tvVideo->id,
            'name' => 'Streaming Media Players',
            'slug' => 'streaming-media-players',
            'description' => 'Chromecast, Roku, Fire TV y dispositivos similares',
        ]);

        Category::factory()->create([
            'parent_id' => $gaming->id,
            'name' => 'Consolas',
            'slug' => 'consolas',
            'description' => 'PlayStation, Xbox, Nintendo y consolas retro',
        ]);

        Category::factory()->create([
            'parent_id' => $gaming->id,
            'name' => 'Accesorios Gamer',
            'slug' => 'accesorios-gamer',
            'description' => 'Joysticks, teclados, mouse y sillas gamer',
        ]);

        Category::factory()->create([
            'parent_id' => $gaming->id,
            'name' => 'Componentes de PC',
            'slug' => 'componentes-de-pc',
            'description' => 'Placas de video, memorias, motherboards y fuentes',
        ]);

        // Subcategorías de Ropa
        $men = Category::factory()->create([
            'parent_id' => $clothing->id,
            'name' => 'Hombre',
            'slug' => 'ropa-hombre',
            'description' => 'Ropa para hombre',
        ]);

        $women = Category::factory()->create([
            'parent_id' => $clothing->id,
            'name' => 'Mujer',
            'slug' => 'ropa-mujer',
            'description' => 'Ropa para mujer',
        ]);

        $kids = Category::factory()->create([
            'parent_id' => $clothing->id,
            'name' => 'Niños',
            'slug' => 'ropa-ninos',
            'description' => 'Ropa para niños',
        ]);

        $footwear = Category::factory()->create([
            'parent_id' => $clothing->id,
            'name' => 'Calzado',
            'slug' => 'calzado',
            'description' => 'Zapatillas, botas, zapatos y sandalias',
        ]);

        $accessories = Category::factory()->create([
            'parent_id' => $clothing->id,
            'name' => 'Accesorios de Moda',
            'slug' => 'accesorios-de-moda',
            'description' => 'Carteras, relojes, lentes y cinturones',
        ]);

        Category::factory()->create([
            'parent_id' => $men->id,
            'name' => 'Remeras',
            'slug' => 'remeras-hombre',
            'description' => 'Remeras básicas, estampadas y deportivas para hombre',
        ]);

        Category::factory()->create([
            'parent_id' => $men->id,
            'name' => 'Jeans',
            'slug' => 'jeans-hombre',
            'description' => 'Pantalones jeans para hombre',
        ]);

        Category::factory()->create([
            'parent_id' => $men->id,
            'name' => 'Camisas',
            'slug' => 'camisas-hombre',
            'description' => 'Camisas casuales y formales para hombre',
        ]);

        Category::factory()->create([
            'parent_id' => $women->id,
            'name' => 'Vestidos',
            'slug' => 'vestidos-mujer',
            'description' => 'Vestidos casuales, de fiesta y urbanos',
        ]);

        Category::factory()->create([
            'parent_id' => $women->id,
            'name' => 'Blusas',
            'slug' => 'blusas-mujer',
            'description' => 'Blusas y camisas femeninas',
        ]);

        Category::factory()->create([
            'parent_id' => $women->id,
            'name' => 'Jeans',
            'slug' => 'jeans-mujer',
            'description' => 'Pantalones jeans para mujer',
        ]);

        Category::factory()->create([
            'parent_id' => $kids->id,
            'name' => 'Bebés',
            'slug' => 'ropa-bebes',
            'description' => 'Indumentaria para bebés',
        ]);

        Category::factory()->create([
            'parent_id' => $kids->id,
            'name' => 'Niñas',
            'slug' => 'ropa-ninas',
            'description' => 'Indumentaria para niñas',
        ]);

        Category::factory()->create([
            'parent_id' => $kids->id,
            'name' => 'Niños Varones',
            'slug' => 'ropa-ninos-varones',
            'description' => 'Indumentaria para niños',
        ]);

        Category::factory()->create([
            'parent_id' => $footwear->id,
            'name' => 'Zapatillas',
            'slug' => 'zapatillas',
            'description' => 'Zapatillas urbanas y deportivas',
        ]);

        Category::factory()->create([
            'parent_id' => $footwear->id,
            'name' => 'Botas',
            'slug' => 'botas',
            'description' => 'Botas y botinetas para todas las temporadas',
        ]);

        Category::factory()->create([
            'parent_id' => $footwear->id,
            'name' => 'Sandalias',
            'slug' => 'sandalias',
            'description' => 'Sandalias, ojotas y calzado fresco',
        ]);

        Category::factory()->create([
            'parent_id' => $accessories->id,
            'name' => 'Mochilas',
            'slug' => 'mochilas',
            'description' => 'Mochilas urbanas, escolares y de viaje',
        ]);

        Category::factory()->create([
            'parent_id' => $accessories->id,
            'name' => 'Relojes',
            'slug' => 'relojes',
            'description' => 'Relojes analógicos, digitales y smartwatches',
        ]);

        Category::factory()->create([
            'parent_id' => $accessories->id,
            'name' => 'Lentes de Sol',
            'slug' => 'lentes-de-sol',
            'description' => 'Anteojos de sol y accesorios ópticos',
        ]);

        // Subcategorías de Hogar
        $furniture = Category::factory()->create([
            'parent_id' => $home->id,
            'name' => 'Muebles',
            'slug' => 'muebles',
            'description' => 'Muebles para el hogar',
        ]);

        $decoration = Category::factory()->create([
            'parent_id' => $home->id,
            'name' => 'Decoración',
            'slug' => 'decoracion',
            'description' => 'Artículos de decoración',
        ]);

        $appliances = Category::factory()->create([
            'parent_id' => $home->id,
            'name' => 'Electrodomésticos',
            'slug' => 'electrodomesticos',
            'description' => 'Electrodomésticos de cocina, limpieza y confort',
        ]);

        $garden = Category::factory()->create([
            'parent_id' => $home->id,
            'name' => 'Jardín y Exterior',
            'slug' => 'jardin-y-exterior',
            'description' => 'Muebles, herramientas y decoración exterior',
        ]);

        Category::factory()->create([
            'parent_id' => $furniture->id,
            'name' => 'Sofás y Sillones',
            'slug' => 'sofas-y-sillones',
            'description' => 'Sofás, sillones reclinables y futones',
        ]);

        Category::factory()->create([
            'parent_id' => $furniture->id,
            'name' => 'Mesas',
            'slug' => 'mesas',
            'description' => 'Mesas ratonas, de comedor y auxiliares',
        ]);

        Category::factory()->create([
            'parent_id' => $furniture->id,
            'name' => 'Placares y Roperos',
            'slug' => 'placares-y-roperos',
            'description' => 'Guardado y organización para dormitorios',
        ]);

        Category::factory()->create([
            'parent_id' => $decoration->id,
            'name' => 'Cuadros',
            'slug' => 'cuadros',
            'description' => 'Cuadros decorativos, láminas y posters',
        ]);

        Category::factory()->create([
            'parent_id' => $decoration->id,
            'name' => 'Espejos',
            'slug' => 'espejos',
            'description' => 'Espejos decorativos y funcionales',
        ]);

        Category::factory()->create([
            'parent_id' => $decoration->id,
            'name' => 'Alfombras',
            'slug' => 'alfombras',
            'description' => 'Alfombras para living, dormitorio y pasillos',
        ]);

        Category::factory()->create([
            'parent_id' => $appliances->id,
            'name' => 'Heladeras',
            'slug' => 'heladeras',
            'description' => 'Heladeras con freezer, no frost e inverter',
        ]);

        Category::factory()->create([
            'parent_id' => $appliances->id,
            'name' => 'Lavarropas',
            'slug' => 'lavarropas',
            'description' => 'Lavarropas automáticos y semiautomáticos',
        ]);

        Category::factory()->create([
            'parent_id' => $appliances->id,
            'name' => 'Pequeños Electrodomésticos',
            'slug' => 'pequenos-electrodomesticos',
            'description' => 'Cafeteras, licuadoras, freidoras y pavas eléctricas',
        ]);

        Category::factory()->create([
            'parent_id' => $garden->id,
            'name' => 'Muebles de Jardín',
            'slug' => 'muebles-de-jardin',
            'description' => 'Sillas, mesas y juegos para exterior',
        ]);

        Category::factory()->create([
            'parent_id' => $garden->id,
            'name' => 'Parrillas',
            'slug' => 'parrillas',
            'description' => 'Parrillas, accesorios y utensilios para asado',
        ]);

        Category::factory()->create([
            'parent_id' => $garden->id,
            'name' => 'Herramientas de Jardín',
            'slug' => 'herramientas-de-jardin',
            'description' => 'Mangueras, podadoras y herramientas manuales',
        ]);

        // Subcategorías de Belleza
        $hairCare = Category::factory()->create([
            'parent_id' => $beauty->id,
            'name' => 'Cuidado del Cabello',
            'slug' => 'cuidado-del-cabello',
            'description' => 'Productos para limpieza, peinado y tratamiento capilar',
        ]);

        $skinCare = Category::factory()->create([
            'parent_id' => $beauty->id,
            'name' => 'Cuidado de la Piel',
            'slug' => 'cuidado-de-la-piel',
            'description' => 'Rutinas faciales y corporales',
        ]);

        $makeup = Category::factory()->create([
            'parent_id' => $beauty->id,
            'name' => 'Maquillaje',
            'slug' => 'maquillaje',
            'description' => 'Cosméticos para rostro, ojos y labios',
        ]);

        Category::factory()->create([
            'parent_id' => $hairCare->id,
            'name' => 'Shampoo',
            'slug' => 'shampoo',
            'description' => 'Shampoos para distintos tipos de cabello',
        ]);

        Category::factory()->create([
            'parent_id' => $hairCare->id,
            'name' => 'Acondicionadores',
            'slug' => 'acondicionadores',
            'description' => 'Acondicionadores y tratamientos nutritivos',
        ]);

        Category::factory()->create([
            'parent_id' => $hairCare->id,
            'name' => 'Planchitas y Secadores',
            'slug' => 'planchitas-y-secadores',
            'description' => 'Herramientas eléctricas para peinado',
        ]);

        Category::factory()->create([
            'parent_id' => $skinCare->id,
            'name' => 'Limpieza Facial',
            'slug' => 'limpieza-facial',
            'description' => 'Espumas, geles y aguas micelares',
        ]);

        Category::factory()->create([
            'parent_id' => $skinCare->id,
            'name' => 'Hidratantes',
            'slug' => 'hidratantes',
            'description' => 'Cremas, sérums y lociones hidratantes',
        ]);

        Category::factory()->create([
            'parent_id' => $skinCare->id,
            'name' => 'Protección Solar',
            'slug' => 'proteccion-solar',
            'description' => 'Protectores solares faciales y corporales',
        ]);

        Category::factory()->create([
            'parent_id' => $makeup->id,
            'name' => 'Bases y Correctores',
            'slug' => 'bases-y-correctores',
            'description' => 'Maquillaje para rostro y cobertura',
        ]);

        Category::factory()->create([
            'parent_id' => $makeup->id,
            'name' => 'Labiales',
            'slug' => 'labiales',
            'description' => 'Labiales líquidos, mate y gloss',
        ]);

        Category::factory()->create([
            'parent_id' => $makeup->id,
            'name' => 'Máscaras de Pestañas',
            'slug' => 'mascaras-de-pestanas',
            'description' => 'Máscaras para volumen y definición',
        ]);

        // Subcategorías de Deportes
        $fitness = Category::factory()->create([
            'parent_id' => $sports->id,
            'name' => 'Fitness',
            'slug' => 'fitness',
            'description' => 'Equipamiento para entrenamiento y musculación',
        ]);

        $running = Category::factory()->create([
            'parent_id' => $sports->id,
            'name' => 'Running',
            'slug' => 'running',
            'description' => 'Accesorios e indumentaria para correr',
        ]);

        $cycling = Category::factory()->create([
            'parent_id' => $sports->id,
            'name' => 'Ciclismo',
            'slug' => 'ciclismo',
            'description' => 'Bicicletas, cascos y accesorios',
        ]);

        Category::factory()->create([
            'parent_id' => $fitness->id,
            'name' => 'Mancuernas',
            'slug' => 'mancuernas',
            'description' => 'Mancuernas, pesas rusas y kits de entrenamiento',
        ]);

        Category::factory()->create([
            'parent_id' => $fitness->id,
            'name' => 'Bancos de Musculación',
            'slug' => 'bancos-de-musculacion',
            'description' => 'Bancos y soportes para entrenamiento de fuerza',
        ]);

        Category::factory()->create([
            'parent_id' => $fitness->id,
            'name' => 'Bandas Elásticas',
            'slug' => 'bandas-elasticas',
            'description' => 'Bandas de resistencia y accesorios funcionales',
        ]);

        Category::factory()->create([
            'parent_id' => $running->id,
            'name' => 'Zapatillas de Running',
            'slug' => 'zapatillas-de-running',
            'description' => 'Calzado técnico para running',
        ]);

        Category::factory()->create([
            'parent_id' => $running->id,
            'name' => 'Relojes Deportivos',
            'slug' => 'relojes-deportivos',
            'description' => 'Relojes GPS y smartwatches deportivos',
        ]);

        Category::factory()->create([
            'parent_id' => $running->id,
            'name' => 'Hidratación Deportiva',
            'slug' => 'hidratacion-deportiva',
            'description' => 'Botellas, mochilas y cinturones de hidratación',
        ]);

        Category::factory()->create([
            'parent_id' => $cycling->id,
            'name' => 'Bicicletas',
            'slug' => 'bicicletas',
            'description' => 'Bicicletas urbanas, mountain bike y ruta',
        ]);

        Category::factory()->create([
            'parent_id' => $cycling->id,
            'name' => 'Cascos',
            'slug' => 'cascos-ciclismo',
            'description' => 'Cascos de protección para ciclismo',
        ]);

        Category::factory()->create([
            'parent_id' => $cycling->id,
            'name' => 'Accesorios para Bicicleta',
            'slug' => 'accesorios-para-bicicleta',
            'description' => 'Luces, infladores, caramañolas y herramientas',
        ]);

        // Subcategorías de Bebés
        $walk = Category::factory()->create([
            'parent_id' => $babies->id,
            'name' => 'Paseo',
            'slug' => 'paseo-bebes',
            'description' => 'Cochecitos, sillas para auto y portabebés',
        ]);

        $feeding = Category::factory()->create([
            'parent_id' => $babies->id,
            'name' => 'Alimentación',
            'slug' => 'alimentacion-bebes',
            'description' => 'Mamaderas, extractores y sillas de comer',
        ]);

        $sleep = Category::factory()->create([
            'parent_id' => $babies->id,
            'name' => 'Descanso',
            'slug' => 'descanso-bebes',
            'description' => 'Cunas, colchones y monitores para bebé',
        ]);

        Category::factory()->create([
            'parent_id' => $walk->id,
            'name' => 'Cochecitos',
            'slug' => 'cochecitos',
            'description' => 'Cochecitos travel system, jogging y paragüitas',
        ]);

        Category::factory()->create([
            'parent_id' => $walk->id,
            'name' => 'Sillas para Auto',
            'slug' => 'sillas-para-auto',
            'description' => 'Butacas y boosters para auto',
        ]);

        Category::factory()->create([
            'parent_id' => $walk->id,
            'name' => 'Mochilas Portabebés',
            'slug' => 'mochilas-portabebes',
            'description' => 'Portabebés ergonómicos y fulares',
        ]);

        Category::factory()->create([
            'parent_id' => $feeding->id,
            'name' => 'Mamaderas',
            'slug' => 'mamaderas',
            'description' => 'Mamaderas anticólicos y accesorios',
        ]);

        Category::factory()->create([
            'parent_id' => $feeding->id,
            'name' => 'Sillas de Comer',
            'slug' => 'sillas-de-comer',
            'description' => 'Sillas altas y boosters para bebés',
        ]);

        Category::factory()->create([
            'parent_id' => $feeding->id,
            'name' => 'Extractores de Leche',
            'slug' => 'extractores-de-leche',
            'description' => 'Extractores manuales y eléctricos',
        ]);

        Category::factory()->create([
            'parent_id' => $sleep->id,
            'name' => 'Cunas',
            'slug' => 'cunas',
            'description' => 'Cunas funcionales, colecho y practicunas',
        ]);

        Category::factory()->create([
            'parent_id' => $sleep->id,
            'name' => 'Colchones para Bebé',
            'slug' => 'colchones-para-bebe',
            'description' => 'Colchones y accesorios para descanso infantil',
        ]);

        Category::factory()->create([
            'parent_id' => $sleep->id,
            'name' => 'Monitores para Bebé',
            'slug' => 'monitores-para-bebe',
            'description' => 'Baby call con audio, video y WiFi',
        ]);

        // Subcategorías de Supermercado
        $grocery = Category::factory()->create([
            'parent_id' => $supermarket->id,
            'name' => 'Almacén',
            'slug' => 'almacen',
            'description' => 'Productos secos y básicos de despensa',
        ]);

        $drinks = Category::factory()->create([
            'parent_id' => $supermarket->id,
            'name' => 'Bebidas',
            'slug' => 'bebidas',
            'description' => 'Aguas, gaseosas, jugos y bebidas alcohólicas',
        ]);

        $cleaning = Category::factory()->create([
            'parent_id' => $supermarket->id,
            'name' => 'Limpieza',
            'slug' => 'limpieza',
            'description' => 'Productos de limpieza para hogar y ropa',
        ]);

        Category::factory()->create([
            'parent_id' => $grocery->id,
            'name' => 'Arroz y Legumbres',
            'slug' => 'arroz-y-legumbres',
            'description' => 'Arroces, lentejas, garbanzos y porotos',
        ]);

        Category::factory()->create([
            'parent_id' => $grocery->id,
            'name' => 'Pastas',
            'slug' => 'pastas',
            'description' => 'Fideos, spaghetti y variedades secas',
        ]);

        Category::factory()->create([
            'parent_id' => $grocery->id,
            'name' => 'Aceites y Vinagres',
            'slug' => 'aceites-y-vinagres',
            'description' => 'Aceites de cocina, oliva y vinagres',
        ]);

        Category::factory()->create([
            'parent_id' => $drinks->id,
            'name' => 'Gaseosas',
            'slug' => 'gaseosas',
            'description' => 'Bebidas carbonatadas y saborizadas',
        ]);

        Category::factory()->create([
            'parent_id' => $drinks->id,
            'name' => 'Aguas',
            'slug' => 'aguas',
            'description' => 'Aguas minerales, saborizadas y soda',
        ]);

        Category::factory()->create([
            'parent_id' => $drinks->id,
            'name' => 'Cervezas y Vinos',
            'slug' => 'cervezas-y-vinos',
            'description' => 'Bebidas alcohólicas para consumo y eventos',
        ]);

        Category::factory()->create([
            'parent_id' => $cleaning->id,
            'name' => 'Detergentes',
            'slug' => 'detergentes',
            'description' => 'Detergentes para vajilla y cocina',
        ]);

        Category::factory()->create([
            'parent_id' => $cleaning->id,
            'name' => 'Desinfectantes',
            'slug' => 'desinfectantes',
            'description' => 'Lavandina, desinfectantes y sanitizantes',
        ]);

        Category::factory()->create([
            'parent_id' => $cleaning->id,
            'name' => 'Lavado de Ropa',
            'slug' => 'lavado-de-ropa',
            'description' => 'Jabones líquidos, suavizantes y quitamanchas',
        ]);
    }
}
