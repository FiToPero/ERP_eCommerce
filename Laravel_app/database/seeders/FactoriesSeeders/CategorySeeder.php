<?php

namespace Database\Seeders\FactoriesSeeders;

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
        $electronics = $this->findOrCreateCategory('electronica', [
            'name' => 'Electrónica',
            'description' => 'Productos electrónicos',
        ]);

        $clothing = $this->findOrCreateCategory('ropa', [
            'name' => 'Ropa',
            'description' => 'Ropa y accesorios',
        ]);

        $home = $this->findOrCreateCategory('hogar', [
            'name' => 'Hogar',
            'description' => 'Productos para el hogar',
        ]);

        $beauty = $this->findOrCreateCategory('belleza', [
            'name' => 'Belleza',
            'description' => 'Belleza, cosmética y cuidado personal',
        ]);

        $sports = $this->findOrCreateCategory('deportes', [
            'name' => 'Deportes',
            'description' => 'Artículos deportivos y fitness',
        ]);

        $babies = $this->findOrCreateCategory('bebes', [
            'name' => 'Bebés',
            'description' => 'Productos para bebés y primera infancia',
        ]);

        $supermarket = $this->findOrCreateCategory('supermercado', [
            'name' => 'Supermercado',
            'description' => 'Alimentos, bebidas y limpieza',
        ]);

        // Subcategorías de Electrónica
        $smartphones = $this->childCategory($electronics, 'smartphones', [
            'name' => 'Smartphones',
            'description' => 'Teléfonos inteligentes',
        ]);

        $laptops = $this->childCategory($electronics, 'laptops', [
            'name' => 'Laptops',
            'description' => 'Computadoras portátiles',
        ]);

        $tablets = $this->childCategory($electronics, 'tablets', [
            'name' => 'Tablets',
            'description' => 'Tabletas',
        ]);

        $audio = $this->childCategory($electronics, 'audio', [
            'name' => 'Audio',
            'description' => 'Auriculares, parlantes y equipos de sonido',
        ]);

        $tvVideo = $this->childCategory($electronics, 'tv-y-video', [
            'name' => 'TV y Video',
            'description' => 'Televisores, proyectores y streaming',
        ]);

        $gaming = $this->childCategory($electronics, 'gaming', [
            'name' => 'Gaming',
            'description' => 'Consolas, accesorios y componentes gamer',
        ]);

        // Sub-subcategorías de Smartphones (3er nivel)
        $this->childCategory($smartphones, 'iphone', [
            'name' => 'iPhone',
            'description' => 'Smartphones Apple iPhone',
        ]);

        $this->childCategory($smartphones, 'samsung-galaxy', [
            'name' => 'Samsung Galaxy',
            'description' => 'Smartphones Samsung Galaxy',
        ]);

        $this->childCategory($smartphones, 'motorola', [
            'name' => 'Motorola',
            'description' => 'Smartphones Motorola',
        ]);

        $this->childCategory($smartphones, 'xiaomi', [
            'name' => 'Xiaomi',
            'description' => 'Smartphones Xiaomi y Redmi',
        ]);

        $this->childCategory($smartphones, 'accesorios-para-celulares', [
            'name' => 'Accesorios para Celulares',
            'description' => 'Fundas, cables, cargadores y soportes',
        ]);

        $this->childCategory($laptops, 'ultrabooks', [
            'name' => 'Ultrabooks',
            'description' => 'Laptops livianas y portátiles de alto rendimiento',
        ]);

        $this->childCategory($laptops, 'laptops-gamer', [
            'name' => 'Laptops Gamer',
            'description' => 'Portátiles para gaming y alto desempeño gráfico',
        ]);

        $this->childCategory($laptops, 'accesorios-para-laptops', [
            'name' => 'Accesorios para Laptops',
            'description' => 'Bases, cargadores, mochilas y hubs',
        ]);

        $this->childCategory($tablets, 'ipad', [
            'name' => 'iPad',
            'description' => 'Tablets Apple iPad',
        ]);

        $this->childCategory($tablets, 'tablets-android', [
            'name' => 'Tablets Android',
            'description' => 'Tablets Samsung, Lenovo, Xiaomi y otras',
        ]);

        $this->childCategory($tablets, 'accesorios-para-tablets', [
            'name' => 'Accesorios para Tablets',
            'description' => 'Fundas con teclado, stylus y protectores',
        ]);

        $this->childCategory($audio, 'auriculares', [
            'name' => 'Auriculares',
            'description' => 'Auriculares inalámbricos, gamers e in-ear',
        ]);

        $this->childCategory($audio, 'parlantes', [
            'name' => 'Parlantes',
            'description' => 'Parlantes Bluetooth, portátiles y smart speakers',
        ]);

        $this->childCategory($audio, 'microfonos', [
            'name' => 'Micrófonos',
            'description' => 'Micrófonos para streaming, estudio y podcast',
        ]);

        $this->childCategory($tvVideo, 'smart-tv', [
            'name' => 'Smart TV',
            'description' => 'Televisores con conectividad y apps integradas',
        ]);

        $this->childCategory($tvVideo, 'proyectores', [
            'name' => 'Proyectores',
            'description' => 'Proyectores portátiles, hogareños y profesionales',
        ]);

        $this->childCategory($tvVideo, 'streaming-media-players', [
            'name' => 'Streaming Media Players',
            'description' => 'Chromecast, Roku, Fire TV y dispositivos similares',
        ]);

        $this->childCategory($gaming, 'consolas', [
            'name' => 'Consolas',
            'description' => 'PlayStation, Xbox, Nintendo y consolas retro',
        ]);

        $this->childCategory($gaming, 'accesorios-gamer', [
            'name' => 'Accesorios Gamer',
            'description' => 'Joysticks, teclados, mouse y sillas gamer',
        ]);

        $this->childCategory($gaming, 'componentes-de-pc', [
            'name' => 'Componentes de PC',
            'description' => 'Placas de video, memorias, motherboards y fuentes',
        ]);

        // Subcategorías de Ropa
        $men = $this->childCategory($clothing, 'ropa-hombre', [
            'name' => 'Hombre',
            'description' => 'Ropa para hombre',
        ]);

        $women = $this->childCategory($clothing, 'ropa-mujer', [
            'name' => 'Mujer',
            'description' => 'Ropa para mujer',
        ]);

        $kids = $this->childCategory($clothing, 'ropa-ninos', [
            'name' => 'Niños',
            'description' => 'Ropa para niños',
        ]);

        $footwear = $this->childCategory($clothing, 'calzado', [
            'name' => 'Calzado',
            'description' => 'Zapatillas, botas, zapatos y sandalias',
        ]);

        $accessories = $this->childCategory($clothing, 'accesorios-de-moda', [
            'name' => 'Accesorios de Moda',
            'description' => 'Carteras, relojes, lentes y cinturones',
        ]);

        $this->childCategory($men, 'remeras-hombre', [
            'name' => 'Remeras',
            'description' => 'Remeras básicas, estampadas y deportivas para hombre',
        ]);

        $this->childCategory($men, 'jeans-hombre', [
            'name' => 'Jeans',
            'description' => 'Pantalones jeans para hombre',
        ]);

        $this->childCategory($men, 'camisas-hombre', [
            'name' => 'Camisas',
            'description' => 'Camisas casuales y formales para hombre',
        ]);

        $this->childCategory($women, 'vestidos-mujer', [
            'name' => 'Vestidos',
            'description' => 'Vestidos casuales, de fiesta y urbanos',
        ]);

        $this->childCategory($women, 'blusas-mujer', [
            'name' => 'Blusas',
            'description' => 'Blusas y camisas femeninas',
        ]);

        $this->childCategory($women, 'jeans-mujer', [
            'name' => 'Jeans',
            'description' => 'Pantalones jeans para mujer',
        ]);

        $this->childCategory($kids, 'ropa-bebes', [
            'name' => 'Bebés',
            'description' => 'Indumentaria para bebés',
        ]);

        $this->childCategory($kids, 'ropa-ninas', [
            'name' => 'Niñas',
            'description' => 'Indumentaria para niñas',
        ]);

        $this->childCategory($kids, 'ropa-ninos-varones', [
            'name' => 'Niños Varones',
            'description' => 'Indumentaria para niños',
        ]);

        $this->childCategory($footwear, 'zapatillas', [
            'name' => 'Zapatillas',
            'description' => 'Zapatillas urbanas y deportivas',
        ]);

        $this->childCategory($footwear, 'botas', [
            'name' => 'Botas',
            'description' => 'Botas y botinetas para todas las temporadas',
        ]);

        $this->childCategory($footwear, 'sandalias', [
            'name' => 'Sandalias',
            'description' => 'Sandalias, ojotas y calzado fresco',
        ]);

        $this->childCategory($accessories, 'mochilas', [
            'name' => 'Mochilas',
            'description' => 'Mochilas urbanas, escolares y de viaje',
        ]);

        $this->childCategory($accessories, 'relojes', [
            'name' => 'Relojes',
            'description' => 'Relojes analógicos, digitales y smartwatches',
        ]);

        $this->childCategory($accessories, 'lentes-de-sol', [
            'name' => 'Lentes de Sol',
            'description' => 'Anteojos de sol y accesorios ópticos',
        ]);

        // Subcategorías de Hogar
        $furniture = $this->childCategory($home, 'muebles', [
            'name' => 'Muebles',
            'description' => 'Muebles para el hogar',
        ]);

        $decoration = $this->childCategory($home, 'decoracion', [
            'name' => 'Decoración',
            'description' => 'Artículos de decoración',
        ]);

        $appliances = $this->childCategory($home, 'electrodomesticos', [
            'name' => 'Electrodomésticos',
            'description' => 'Electrodomésticos de cocina, limpieza y confort',
        ]);

        $garden = $this->childCategory($home, 'jardin-y-exterior', [
            'name' => 'Jardín y Exterior',
            'description' => 'Muebles, herramientas y decoración exterior',
        ]);

        $this->childCategory($furniture, 'sofas-y-sillones', [
            'name' => 'Sofás y Sillones',
            'description' => 'Sofás, sillones reclinables y futones',
        ]);

        $this->childCategory($furniture, 'mesas', [
            'name' => 'Mesas',
            'description' => 'Mesas ratonas, de comedor y auxiliares',
        ]);

        $this->childCategory($furniture, 'placares-y-roperos', [
            'name' => 'Placares y Roperos',
            'description' => 'Guardado y organización para dormitorios',
        ]);

        $this->childCategory($decoration, 'cuadros', [
            'name' => 'Cuadros',
            'description' => 'Cuadros decorativos, láminas y posters',
        ]);

        $this->childCategory($decoration, 'espejos', [
            'name' => 'Espejos',
            'description' => 'Espejos decorativos y funcionales',
        ]);

        $this->childCategory($decoration, 'alfombras', [
            'name' => 'Alfombras',
            'description' => 'Alfombras para living, dormitorio y pasillos',
        ]);

        $this->childCategory($appliances, 'heladeras', [
            'name' => 'Heladeras',
            'description' => 'Heladeras con freezer, no frost e inverter',
        ]);

        $this->childCategory($appliances, 'lavarropas', [
            'name' => 'Lavarropas',
            'description' => 'Lavarropas automáticos y semiautomáticos',
        ]);

        $this->childCategory($appliances, 'pequenos-electrodomesticos', [
            'name' => 'Pequeños Electrodomésticos',
            'description' => 'Cafeteras, licuadoras, freidoras y pavas eléctricas',
        ]);

        $this->childCategory($garden, 'muebles-de-jardin', [
            'name' => 'Muebles de Jardín',
            'description' => 'Sillas, mesas y juegos para exterior',
        ]);

        $this->childCategory($garden, 'parrillas', [
            'name' => 'Parrillas',
            'description' => 'Parrillas, accesorios y utensilios para asado',
        ]);

        $this->childCategory($garden, 'herramientas-de-jardin', [
            'name' => 'Herramientas de Jardín',
            'description' => 'Mangueras, podadoras y herramientas manuales',
        ]);

        // Subcategorías de Belleza
        $hairCare = $this->childCategory($beauty, 'cuidado-del-cabello', [
            'name' => 'Cuidado del Cabello',
            'description' => 'Productos para limpieza, peinado y tratamiento capilar',
        ]);

        $skinCare = $this->childCategory($beauty, 'cuidado-de-la-piel', [
            'name' => 'Cuidado de la Piel',
            'description' => 'Rutinas faciales y corporales',
        ]);

        $makeup = $this->childCategory($beauty, 'maquillaje', [
            'name' => 'Maquillaje',
            'description' => 'Cosméticos para rostro, ojos y labios',
        ]);

        $this->childCategory($hairCare, 'shampoo', [
            'name' => 'Shampoo',
            'description' => 'Shampoos para distintos tipos de cabello',
        ]);

        $this->childCategory($hairCare, 'acondicionadores', [
            'name' => 'Acondicionadores',
            'description' => 'Acondicionadores y tratamientos nutritivos',
        ]);

        $this->childCategory($hairCare, 'planchitas-y-secadores', [
            'name' => 'Planchitas y Secadores',
            'description' => 'Herramientas eléctricas para peinado',
        ]);

        $this->childCategory($skinCare, 'limpieza-facial', [
            'name' => 'Limpieza Facial',
            'description' => 'Espumas, geles y aguas micelares',
        ]);

        $this->childCategory($skinCare, 'hidratantes', [
            'name' => 'Hidratantes',
            'description' => 'Cremas, sérums y lociones hidratantes',
        ]);

        $this->childCategory($skinCare, 'proteccion-solar', [
            'name' => 'Protección Solar',
            'description' => 'Protectores solares faciales y corporales',
        ]);

        $this->childCategory($makeup, 'bases-y-correctores', [
            'name' => 'Bases y Correctores',
            'description' => 'Maquillaje para rostro y cobertura',
        ]);

        $this->childCategory($makeup, 'labiales', [
            'name' => 'Labiales',
            'description' => 'Labiales líquidos, mate y gloss',
        ]);

        $this->childCategory($makeup, 'mascaras-de-pestanas', [
            'name' => 'Máscaras de Pestañas',
            'description' => 'Máscaras para volumen y definición',
        ]);

        // Subcategorías de Deportes
        $fitness = $this->childCategory($sports, 'fitness', [
            'name' => 'Fitness',
            'description' => 'Equipamiento para entrenamiento y musculación',
        ]);

        $running = $this->childCategory($sports, 'running', [
            'name' => 'Running',
            'description' => 'Accesorios e indumentaria para correr',
        ]);

        $cycling = $this->childCategory($sports, 'ciclismo', [
            'name' => 'Ciclismo',
            'description' => 'Bicicletas, cascos y accesorios',
        ]);

        $this->childCategory($fitness, 'mancuernas', [
            'name' => 'Mancuernas',
            'description' => 'Mancuernas, pesas rusas y kits de entrenamiento',
        ]);

        $this->childCategory($fitness, 'bancos-de-musculacion', [
            'name' => 'Bancos de Musculación',
            'description' => 'Bancos y soportes para entrenamiento de fuerza',
        ]);

        $this->childCategory($fitness, 'bandas-elasticas', [
            'name' => 'Bandas Elásticas',
            'description' => 'Bandas de resistencia y accesorios funcionales',
        ]);

        $this->childCategory($running, 'zapatillas-de-running', [
            'name' => 'Zapatillas de Running',
            'description' => 'Calzado técnico para running',
        ]);

        $this->childCategory($running, 'relojes-deportivos', [
            'name' => 'Relojes Deportivos',
            'description' => 'Relojes GPS y smartwatches deportivos',
        ]);

        $this->childCategory($running, 'hidratacion-deportiva', [
            'name' => 'Hidratación Deportiva',
            'description' => 'Botellas, mochilas y cinturones de hidratación',
        ]);

        $this->childCategory($cycling, 'bicicletas', [
            'name' => 'Bicicletas',
            'description' => 'Bicicletas urbanas, mountain bike y ruta',
        ]);

        $this->childCategory($cycling, 'cascos-ciclismo', [
            'name' => 'Cascos',
            'description' => 'Cascos de protección para ciclismo',
        ]);

        $this->childCategory($cycling, 'accesorios-para-bicicleta', [
            'name' => 'Accesorios para Bicicleta',
            'description' => 'Luces, infladores, caramañolas y herramientas',
        ]);

        // Subcategorías de Bebés
        $walk = $this->childCategory($babies, 'paseo-bebes', [
            'name' => 'Paseo',
            'description' => 'Cochecitos, sillas para auto y portabebés',
        ]);

        $feeding = $this->childCategory($babies, 'alimentacion-bebes', [
            'name' => 'Alimentación',
            'description' => 'Mamaderas, extractores y sillas de comer',
        ]);

        $sleep = $this->childCategory($babies, 'descanso-bebes', [
            'name' => 'Descanso',
            'description' => 'Cunas, colchones y monitores para bebé',
        ]);

        $this->childCategory($walk, 'cochecitos', [
            'name' => 'Cochecitos',
            'description' => 'Cochecitos travel system, jogging y paragüitas',
        ]);

        $this->childCategory($walk, 'sillas-para-auto', [
            'name' => 'Sillas para Auto',
            'description' => 'Butacas y boosters para auto',
        ]);

        $this->childCategory($walk, 'mochilas-portabebes', [
            'name' => 'Mochilas Portabebés',
            'description' => 'Portabebés ergonómicos y fulares',
        ]);

        $this->childCategory($feeding, 'mamaderas', [
            'name' => 'Mamaderas',
            'description' => 'Mamaderas anticólicos y accesorios',
        ]);

        $this->childCategory($feeding, 'sillas-de-comer', [
            'name' => 'Sillas de Comer',
            'description' => 'Sillas altas y boosters para bebés',
        ]);

        $this->childCategory($feeding, 'extractores-de-leche', [
            'name' => 'Extractores de Leche',
            'description' => 'Extractores manuales y eléctricos',
        ]);

        $this->childCategory($sleep, 'cunas', [
            'name' => 'Cunas',
            'description' => 'Cunas funcionales, colecho y practicunas',
        ]);

        $this->childCategory($sleep, 'colchones-para-bebe', [
            'name' => 'Colchones para Bebé',
            'description' => 'Colchones y accesorios para descanso infantil',
        ]);

        $this->childCategory($sleep, 'monitores-para-bebe', [
            'name' => 'Monitores para Bebé',
            'description' => 'Baby call con audio, video y WiFi',
        ]);

        // Subcategorías de Supermercado
        $grocery = $this->childCategory($supermarket, 'almacen', [
            'name' => 'Almacén',
            'description' => 'Productos secos y básicos de despensa',
        ]);

        $drinks = $this->childCategory($supermarket, 'bebidas', [
            'name' => 'Bebidas',
            'description' => 'Aguas, gaseosas, jugos y bebidas alcohólicas',
        ]);

        $cleaning = $this->childCategory($supermarket, 'limpieza', [
            'name' => 'Limpieza',
            'description' => 'Productos de limpieza para hogar y ropa',
        ]);

        $this->childCategory($grocery, 'arroz-y-legumbres', [
            'name' => 'Arroz y Legumbres',
            'description' => 'Arroces, lentejas, garbanzos y porotos',
        ]);

        $this->childCategory($grocery, 'pastas', [
            'name' => 'Pastas',
            'description' => 'Fideos, spaghetti y variedades secas',
        ]);

        $this->childCategory($grocery, 'aceites-y-vinagres', [
            'name' => 'Aceites y Vinagres',
            'description' => 'Aceites de cocina, oliva y vinagres',
        ]);

        $this->childCategory($drinks, 'gaseosas', [
            'name' => 'Gaseosas',
            'description' => 'Bebidas carbonatadas y saborizadas',
        ]);

        $this->childCategory($drinks, 'aguas', [
            'name' => 'Aguas',
            'description' => 'Aguas minerales, saborizadas y soda',
        ]);

        $this->childCategory($drinks, 'cervezas-y-vinos', [
            'name' => 'Cervezas y Vinos',
            'description' => 'Bebidas alcohólicas para consumo y eventos',
        ]);

        $this->childCategory($cleaning, 'detergentes', [
            'name' => 'Detergentes',
            'description' => 'Detergentes para vajilla y cocina',
        ]);

        $this->childCategory($cleaning, 'desinfectantes', [
            'name' => 'Desinfectantes',
            'description' => 'Lavandina, desinfectantes y sanitizantes',
        ]);

        $this->childCategory($cleaning, 'lavado-de-ropa', [
            'name' => 'Lavado de Ropa',
            'description' => 'Jabones líquidos, suavizantes y quitamanchas',
        ]);
    }

    private function findOrCreateCategory(string $slug, array $attributes): Category
    {
        return Category::withTrashed()->firstOrCreate(['slug' => $slug], $attributes);
    }

    private function childCategory(?Category $parent, string $slug, array $attributes): ?Category
    {
        if ($parent === null || $parent->trashed()) {
            return null;
        }

        return $this->findOrCreateCategory($slug, ['parent_id' => $parent->id, ...$attributes]);
    }
}
