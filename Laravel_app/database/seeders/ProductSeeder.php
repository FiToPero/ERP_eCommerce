<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductGoogle;
use App\Models\ProductMercadolibre;
use App\Models\ProductMeta;
use App\Models\ProductWeb;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->productCatalog() as $entry) {
            $this->createCatalogProduct($entry);
        }
    }

    private function createCatalogProduct(array $entry): void
    {
        $entry = $this->normalizeCatalogEntry($entry);

        $category = Category::where('slug', $entry['category_slug'])->first();

        if (! $category) {
            return;
        }

        $product = Product::updateOrCreate(
            ['sku' => $entry['sku']],
            [
                'category_id' => $category->id,
                'name' => $entry['name'],
                'slug' => $entry['slug'],
                'short_description' => $entry['short_description'],
                'description' => $entry['description'],
                'brand' => $entry['brand'],
                'barcode' => $entry['barcode'],
                'mpn' => $entry['mpn'],
                'identifier_exists' => $entry['identifier_exists'],
                'condition' => $entry['condition'],
                'availability_date' => $entry['availability_date'],
                'image_link' => $entry['image_link'],
                'additional_image_links' => $entry['additional_image_links'],
                'video_link' => $entry['video_link'],
            ]
        );

        $this->createRelatedRecords($product);
    }

    private function normalizeCatalogEntry(array $entry): array
    {
        $slug = $entry['slug'];
        $name = $entry['name'];
        $brand = $entry['brand'] ?? $this->inferBrandFromName($name);
        $barcode = $entry['barcode'] ?? $this->generateBarcode($entry['sku']);
        $mpn = $entry['mpn'] ?? $this->generateMpn($brand, $entry['sku']);
        $shortDescription = $entry['short_description'] ?? 'Producto destacado del catálogo';
        $description = $entry['description'] ?? ($shortDescription . '. Producto publicado con datos completos y coherentes para todos los canales.');

        return [
            'category_slug' => $entry['category_slug'],
            'name' => $name,
            'sku' => $entry['sku'],
            'slug' => $slug,
            'short_description' => $shortDescription,
            'description' => $description,
            'brand' => $brand,
            'barcode' => $barcode,
            'mpn' => $mpn,
            'identifier_exists' => $entry['identifier_exists'] ?? true,
            'condition' => $entry['condition'] ?? 'new',
            'availability_date' => $entry['availability_date'] ?? null,
            'image_link' => $entry['image_link'] ?? $this->defaultImageLink($slug),
            'additional_image_links' => $entry['additional_image_links'] ?? $this->defaultAdditionalImageLinks($slug),
            'video_link' => $entry['video_link'] ?? $this->defaultVideoLink($slug),
        ];
    }

    private function defaultImageLink(string $slug): string
    {
        return 'https://picsum.photos/seed/' . $slug . '/1200/1200';
    }

    private function defaultAdditionalImageLinks(string $slug): array
    {
        return [
            'https://picsum.photos/seed/' . $slug . '-1/1200/1200',
            'https://picsum.photos/seed/' . $slug . '-2/1200/1200',
            'https://picsum.photos/seed/' . $slug . '-3/1200/1200',
        ];
    }

    private function defaultVideoLink(string $slug): string
    {
        return 'https://example.com/videos/' . $slug;
    }

    private function generateBarcode(string $sku): string
    {
        $numeric = preg_replace('/\D+/', '', (string) crc32($sku) . strlen($sku) . crc32(strrev($sku)));

        return substr(str_pad($numeric, 13, '0'), 0, 13);
    }

    private function generateMpn(?string $brand, string $sku): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) ($brand ?: 'GEN')), 0, 4));

        return $prefix . '-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $sku));
    }

    private function inferBrandFromName(string $name): string
    {
        return match (true) {
            str_contains(strtolower($name), 'iphone'), str_contains(strtolower($name), 'ipad'), str_contains(strtolower($name), 'macbook'), str_contains(strtolower($name), 'airpods') => 'Apple',
            str_contains(strtolower($name), 'samsung'), str_contains(strtolower($name), 'galaxy') => 'Samsung',
            str_contains(strtolower($name), 'motorola'), str_contains(strtolower($name), 'moto ') => 'Motorola',
            str_contains(strtolower($name), 'xiaomi'), str_contains(strtolower($name), 'redmi') => 'Xiaomi',
            default => 'Generic',
        };
    }

    private function productCatalog(): array
    {
        return [
            [
                'category_slug' => 'iphone',
                'name' => 'iPhone 15 Pro',
                'sku' => 'IP15PRO-001',
                'slug' => 'iphone-15-pro',
                'short_description' => 'El último iPhone con chip A17 Pro',
                'description' => 'iPhone 15 Pro con pantalla Super Retina XDR de 6.1 pulgadas, chip A17 Pro, cámara principal de 48MP y diseño de titanio.',
                'brand' => 'Apple',
                'barcode' => '0190199559574',
                'mpn' => 'MTXT3LL/A',
                'image_link' => 'https://www.apple.com/newsroom/images/2023/09/apple-introduces-iphone-15-pro-and-iphone-15-pro-max/article/Apple-iPhone-15-Pro-lineup-hero-230912_Full-Bleed-Image.jpg.large.jpg',
                'additional_image_links' => [
                    'https://www.apple.com/v/iphone-15-pro/a/images/overview/welcome/hero_endframe__d19t8cya2x0i_xlarge.jpg',
                    'https://www.apple.com/v/iphone-15-pro/a/images/overview/design/design_hero_endframe__f4saxkdyib2a_xlarge.jpg',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=xqyUdNxWazA',
            ],
            [
                'category_slug' => 'iphone',
                'name' => 'iPhone 14',
                'sku' => 'IP14-001',
                'slug' => 'iphone-14',
                'short_description' => 'iPhone 14 con chip A15 Bionic',
                'description' => 'iPhone 14 con pantalla de 6.1 pulgadas, chip A15 Bionic y sistema de cámara dual avanzado.',
                'brand' => 'Apple',
                'barcode' => '0195949729085',
                'mpn' => 'MPWH3LL/A',
                'image_link' => 'https://www.apple.com/newsroom/images/product/iphone/standard/Apple-iPhone-14-lineup-yellow-hero-230307_big.jpg.large.jpg',
                'additional_image_links' => [
                    'https://www.apple.com/v/iphone-14/a/images/overview/design/colors_midnight__bqgdhxaqc5w2_large.jpg',
                    'https://www.apple.com/v/iphone-14/a/images/overview/camera/camera_hero_endframe__e6khcva4hkeq_large.jpg',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=sa_xd2LxFuc',
            ],
            [
                'category_slug' => 'samsung-galaxy',
                'name' => 'Samsung Galaxy S24 Ultra',
                'sku' => 'SGS24U-001',
                'slug' => 'galaxy-s24-ultra',
                'short_description' => 'El flagship de Samsung con S Pen',
                'description' => 'Galaxy S24 Ultra con pantalla Dynamic AMOLED 2X de 6.8 pulgadas, Snapdragon 8 Gen 3, cámara de 200MP y S Pen integrado.',
                'brand' => 'Samsung',
                'barcode' => '8806095364861',
                'mpn' => 'SM-S928BZKPEUB',
                'image_link' => 'https://images.samsung.com/is/image/samsung/p6pim/ar/2401/gallery/ar-galaxy-s24-s928-sm-s928bzkjaro-thumb-539342727',
                'additional_image_links' => [
                    'https://images.samsung.com/is/image/samsung/assets/latin/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-color-titanium-black-back.jpg',
                    'https://images.samsung.com/is/image/samsung/assets/latin/smartphones/galaxy-s24-ultra/images/galaxy-s24-ultra-highlights-kv.jpg',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=JH8vPBh_mTg',
            ],
            [
                'category_slug' => 'laptops',
                'name' => 'MacBook Pro 14"',
                'sku' => 'MBP14-001',
                'slug' => 'macbook-pro-14',
                'short_description' => 'MacBook Pro con chip M3 Pro',
                'description' => 'MacBook Pro de 14 pulgadas con chip M3 Pro, 18GB de RAM, SSD de 512GB y pantalla Liquid Retina XDR.',
                'brand' => 'Apple',
                'barcode' => '195949628503',
                'mpn' => 'MRX33LL/A',
                'image_link' => 'https://www.apple.com/newsroom/images/2023/10/apple-unveils-new-macbook-pro-featuring-m3-chips/article/Apple-MacBook-Pro-14-inch-space-black-231030_big.jpg.large.jpg',
                'additional_image_links' => [
                    'https://www.apple.com/v/macbook-pro-14-and-16/a/images/overview/hero/hero_intro_endframe__e6khcva4hkeq_large.jpg',
                    'https://www.apple.com/v/macbook-pro-14-and-16/a/images/overview/performance/performance_hero_endframe__b4w3wzd9a8uq_large.jpg',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=ctkW3V0Mh-k',
            ],
            [
                'category_slug' => 'laptops',
                'name' => 'Dell XPS 15',
                'sku' => 'DXPS15-001',
                'slug' => 'dell-xps-15',
                'short_description' => 'Laptop premium con Intel Core i7',
                'description' => 'Dell XPS 15 con procesador Intel Core i7-13700H, 16GB RAM, SSD 512GB y pantalla 15.6 pulgadas FHD+.',
                'brand' => 'Dell',
                'barcode' => '884116346273',
                'mpn' => 'XPS9530-7759SLV-PUS',
                'image_link' => 'https://i.dell.com/is/image/DellContent/content/dam/images/products/laptops-and-2-in-1s/xps/15-9530/media-gallery/silver/laptop-xps-9530-t-sl-gallery-1.psd',
                'additional_image_links' => [
                    'https://i.dell.com/is/image/DellContent/content/dam/images/products/laptops-and-2-in-1s/xps/15-9530/media-gallery/silver/laptop-xps-9530-t-sl-gallery-2.psd',
                    'https://i.dell.com/is/image/DellContent/content/dam/images/products/laptops-and-2-in-1s/xps/15-9530/media-gallery/silver/laptop-xps-9530-t-sl-gallery-3.psd',
                ],
                'video_link' => 'https://www.youtube.com/watch?v=oS4sjC1Z2Aw',
            ],
            [
                'category_slug' => 'motorola',
                'name' => 'Motorola Edge 50 Pro',
                'sku' => 'MOTOEDGE50-001',
                'slug' => 'motorola-edge-50-pro',
                'short_description' => 'Smartphone premium con carga TurboPower',
                'description' => 'Motorola Edge 50 Pro con pantalla pOLED de 144Hz, cámara de 50MP, carga ultrarrápida y diseño delgado.',
                'brand' => 'Motorola',
                'barcode' => '840023270511',
                'mpn' => 'PB1K0004AR',
            ],
            [
                'category_slug' => 'motorola',
                'name' => 'Moto G84 5G',
                'sku' => 'MOTOG84-001',
                'slug' => 'moto-g84-5g',
                'short_description' => 'Gama media equilibrada con OLED',
                'description' => 'Moto G84 5G con pantalla pOLED, procesador Snapdragon, 256GB de almacenamiento y sonido Dolby Atmos.',
                'brand' => 'Motorola',
                'barcode' => '840023251947',
                'mpn' => 'PAYM0037AR',
            ],
            [
                'category_slug' => 'xiaomi',
                'name' => 'Xiaomi 14',
                'sku' => 'XIA14-001',
                'slug' => 'xiaomi-14',
                'short_description' => 'Flagship compacto con óptica Leica',
                'description' => 'Xiaomi 14 con procesador Snapdragon 8 Gen 3, cámaras Leica, pantalla AMOLED LTPO y carga rápida.',
                'brand' => 'Xiaomi',
                'barcode' => '6941812789216',
                'mpn' => '23127PN0CG',
            ],
            [
                'category_slug' => 'xiaomi',
                'name' => 'Redmi Note 13 Pro',
                'sku' => 'REDMIN13P-001',
                'slug' => 'redmi-note-13-pro',
                'short_description' => 'Gran cámara y batería para uso diario',
                'description' => 'Redmi Note 13 Pro con cámara principal de alta resolución, pantalla AMOLED y batería de larga duración.',
                'brand' => 'Xiaomi',
                'barcode' => '6941812774106',
                'mpn' => '23090RA98G',
            ],
            [
                'category_slug' => 'ultrabooks',
                'name' => 'HP Spectre x360 14',
                'sku' => 'HPSPX360-001',
                'slug' => 'hp-spectre-x360-14',
                'short_description' => 'Convertible premium para productividad',
                'description' => 'HP Spectre x360 14 con diseño convertible, pantalla OLED táctil, Intel Core Ultra y gran autonomía.',
                'brand' => 'HP',
                'barcode' => '197029847221',
                'mpn' => '14-EU0003LA',
            ],
            [
                'category_slug' => 'ultrabooks',
                'name' => 'Lenovo Yoga 7i',
                'sku' => 'LENYOGA7I-001',
                'slug' => 'lenovo-yoga-7i',
                'short_description' => 'Ultrabook liviana con bisagra 360',
                'description' => 'Lenovo Yoga 7i con pantalla táctil, construcción en aluminio, SSD veloz y excelente portabilidad.',
                'brand' => 'Lenovo',
                'barcode' => '197529281143',
                'mpn' => '83DK0002AR',
            ],
            [
                'category_slug' => 'laptops-gamer',
                'name' => 'ASUS ROG Strix G16',
                'sku' => 'ASUSROGG16-001',
                'slug' => 'asus-rog-strix-g16',
                'short_description' => 'Gaming de alto rendimiento con RTX',
                'description' => 'Notebook gamer ASUS ROG Strix G16 con Intel Core i9, GPU NVIDIA GeForce RTX y pantalla de alta tasa de refresco.',
                'brand' => 'Asus',
                'barcode' => '197105310911',
                'mpn' => 'G614JI-N4151W',
            ],
            [
                'category_slug' => 'laptops-gamer',
                'name' => 'Acer Predator Helios Neo 16',
                'sku' => 'ACERPHN16-001',
                'slug' => 'acer-predator-helios-neo-16',
                'short_description' => 'Pantalla rápida y potencia para gaming',
                'description' => 'Predator Helios Neo 16 con procesador Intel Core i7, RTX serie 40 y sistema térmico optimizado.',
                'brand' => 'Acer',
                'barcode' => '195133221908',
                'mpn' => 'PHN16-71-78K9',
            ],
            [
                'category_slug' => 'ipad',
                'name' => 'iPad Air 11 M2',
                'sku' => 'IPADAIR11M2-001',
                'slug' => 'ipad-air-11-m2',
                'short_description' => 'Tablet versátil con chip Apple M2',
                'description' => 'iPad Air 11 pulgadas con chip M2, compatibilidad con Apple Pencil Pro y excelente rendimiento para productividad.',
                'brand' => 'Apple',
                'barcode' => '195949996558',
                'mpn' => 'MUWC3LL/A',
            ],
            [
                'category_slug' => 'ipad',
                'name' => 'iPad 10.9 10a Generación',
                'sku' => 'IPAD109-001',
                'slug' => 'ipad-10-9-decima-generacion',
                'short_description' => 'iPad colorida para estudio y ocio',
                'description' => 'iPad de 10.9 pulgadas con chip A14 Bionic, USB-C y pantalla Liquid Retina.',
                'brand' => 'Apple',
                'barcode' => '194253387957',
                'mpn' => 'MPQ13LL/A',
            ],
            [
                'category_slug' => 'tablets-android',
                'name' => 'Samsung Galaxy Tab S9',
                'sku' => 'SGTABS9-001',
                'slug' => 'samsung-galaxy-tab-s9',
                'short_description' => 'Tablet Android premium con S Pen',
                'description' => 'Galaxy Tab S9 con pantalla AMOLED, Snapdragon flagship, resistencia al agua y S Pen incluido.',
                'brand' => 'Samsung',
                'barcode' => '8806095076511',
                'mpn' => 'SM-X710NZAAAFA',
            ],
            [
                'category_slug' => 'tablets-android',
                'name' => 'Lenovo Tab P12',
                'sku' => 'LENTABP12-001',
                'slug' => 'lenovo-tab-p12',
                'short_description' => 'Pantalla amplia para estudio y multimedia',
                'description' => 'Lenovo Tab P12 con panel 3K, cuatro parlantes JBL y batería de larga duración.',
                'brand' => 'Lenovo',
                'barcode' => '197531692299',
                'mpn' => 'ZACH0020AR',
            ],
            [
                'category_slug' => 'auriculares',
                'name' => 'Sony WH-1000XM5',
                'sku' => 'SONYWH1000XM5-001',
                'slug' => 'sony-wh-1000xm5',
                'short_description' => 'Cancelación de ruido líder en su clase',
                'description' => 'Auriculares inalámbricos Sony con cancelación de ruido avanzada, sonido premium y gran autonomía.',
                'brand' => 'Sony',
                'barcode' => '027242923161',
                'mpn' => 'WH1000XM5/B',
            ],
            [
                'category_slug' => 'auriculares',
                'name' => 'AirPods Pro 2',
                'sku' => 'AIRPODSPRO2-001',
                'slug' => 'airpods-pro-2',
                'short_description' => 'Audio espacial y cancelación activa',
                'description' => 'Auriculares in-ear Apple con cancelación activa de ruido, chip H2 y estuche MagSafe.',
                'brand' => 'Apple',
                'barcode' => '194253397390',
                'mpn' => 'MTJV3AM/A',
            ],
            [
                'category_slug' => 'parlantes',
                'name' => 'JBL Charge 5',
                'sku' => 'JBLCHARGE5-001',
                'slug' => 'jbl-charge-5',
                'short_description' => 'Parlante portátil con sonido potente',
                'description' => 'JBL Charge 5 resistente al agua, con batería extendida y graves potentes para interior y exterior.',
                'brand' => 'JBL',
                'barcode' => '6925281982088',
                'mpn' => 'JBLCHARGE5BLKAM',
            ],
            [
                'category_slug' => 'parlantes',
                'name' => 'Google Nest Audio',
                'sku' => 'GOONESTAUDIO-001',
                'slug' => 'google-nest-audio',
                'short_description' => 'Speaker inteligente para el hogar',
                'description' => 'Parlante inteligente Google Nest Audio con Assistant integrado y sonido balanceado.',
                'brand' => 'Google',
                'barcode' => '193575007816',
                'mpn' => 'GA01586-US',
            ],
            [
                'category_slug' => 'microfonos',
                'name' => 'Blue Yeti USB',
                'sku' => 'BLUEYETIUSB-001',
                'slug' => 'blue-yeti-usb',
                'short_description' => 'Micrófono USB para streaming y podcast',
                'description' => 'Blue Yeti USB con múltiples patrones polares y excelente calidad para creadores de contenido.',
                'brand' => 'Logitech',
                'barcode' => '836213000478',
                'mpn' => '988-000100',
            ],
            [
                'category_slug' => 'microfonos',
                'name' => 'Rode NT-USB+',
                'sku' => 'RODENTUSBP-001',
                'slug' => 'rode-nt-usb-plus',
                'short_description' => 'Micrófono condensador USB de estudio',
                'description' => 'Rode NT-USB+ ideal para locución, streaming y grabación de voces con monitoreo directo.',
                'brand' => 'Rode',
                'barcode' => '698813010400',
                'mpn' => 'NTUSBPLUS',
            ],
            [
                'category_slug' => 'smart-tv',
                'name' => 'LG OLED C4 55',
                'sku' => 'LGOLEDC455-001',
                'slug' => 'lg-oled-c4-55',
                'short_description' => 'TV OLED 4K para cine y gaming',
                'description' => 'Smart TV LG OLED C4 de 55 pulgadas con HDMI 2.1, Dolby Vision y panel de gran contraste.',
                'brand' => 'LG',
                'barcode' => '8806096048142',
                'mpn' => 'OLED55C4PSA',
            ],
            [
                'category_slug' => 'smart-tv',
                'name' => 'Samsung QLED Q70D 65',
                'sku' => 'SAMQ70D65-001',
                'slug' => 'samsung-qled-q70d-65',
                'short_description' => 'QLED 4K con excelente brillo y color',
                'description' => 'Smart TV Samsung QLED Q70D de 65 pulgadas con Gaming Hub, Motion Xcelerator y Tizen OS.',
                'brand' => 'Samsung',
                'barcode' => '8806095501228',
                'mpn' => 'QN65Q70DAFXZA',
            ],
            [
                'category_slug' => 'proyectores',
                'name' => 'Epson EpiqVision Flex CO-W01',
                'sku' => 'EPSONCOW01-001',
                'slug' => 'epson-epiqvision-flex-co-w01',
                'short_description' => 'Proyector portátil para oficina y hogar',
                'description' => 'Proyector Epson con gran brillo, resolución WXGA y conectividad práctica para presentaciones y entretenimiento.',
                'brand' => 'Epson',
                'barcode' => '010343964867',
                'mpn' => 'V11HA86020',
            ],
            [
                'category_slug' => 'streaming-media-players',
                'name' => 'Chromecast con Google TV 4K',
                'sku' => 'CHROMECAST4K-001',
                'slug' => 'chromecast-google-tv-4k',
                'short_description' => 'Streaming 4K con control remoto',
                'description' => 'Dispositivo Chromecast con Google TV para apps de streaming, recomendaciones y control por voz.',
                'brand' => 'Google',
                'barcode' => '193575024394',
                'mpn' => 'GA01919-US',
            ],
            [
                'category_slug' => 'consolas',
                'name' => 'PlayStation 5 Slim',
                'sku' => 'PS5SLIM-001',
                'slug' => 'playstation-5-slim',
                'short_description' => 'Consola de nueva generación de Sony',
                'description' => 'PlayStation 5 Slim con almacenamiento SSD ultrarrápido, ray tracing y control DualSense.',
                'brand' => 'Sony',
                'barcode' => '711719579557',
                'mpn' => 'CFI-2015',
            ],
            [
                'category_slug' => 'consolas',
                'name' => 'Xbox Series X',
                'sku' => 'XBOXSX-001',
                'slug' => 'xbox-series-x',
                'short_description' => 'Potencia 4K para gaming exigente',
                'description' => 'Xbox Series X con SSD NVMe, Quick Resume y rendimiento de nueva generación.',
                'brand' => 'Microsoft',
                'barcode' => '889842640724',
                'mpn' => 'RRT-00010',
            ],
            [
                'category_slug' => 'accesorios-gamer',
                'name' => 'Logitech G Pro X Superlight 2',
                'sku' => 'LOGIGPROXSL2-001',
                'slug' => 'logitech-g-pro-x-superlight-2',
                'short_description' => 'Mouse inalámbrico ultraliviano competitivo',
                'description' => 'Mouse gamer Logitech G Pro X Superlight 2 con sensor HERO y diseño de muy bajo peso.',
                'brand' => 'Logitech',
                'barcode' => '097855183316',
                'mpn' => '910-006628',
            ],
            [
                'category_slug' => 'accesorios-gamer',
                'name' => 'Razer BlackWidow V4 Pro',
                'sku' => 'RAZERBWV4P-001',
                'slug' => 'razer-blackwidow-v4-pro',
                'short_description' => 'Teclado mecánico RGB para gaming',
                'description' => 'Teclado mecánico con switches táctiles, rueda de control y retroiluminación RGB avanzada.',
                'brand' => 'Razer',
                'barcode' => '840272910828',
                'mpn' => 'RZ03-04680100-R3U1',
            ],
            [
                'category_slug' => 'componentes-de-pc',
                'name' => 'NVIDIA GeForce RTX 4070 Super',
                'sku' => 'RTX4070S-001',
                'slug' => 'nvidia-geforce-rtx-4070-super',
                'short_description' => 'GPU para gaming y creación de contenido',
                'description' => 'Placa gráfica GeForce RTX 4070 Super con DLSS 3, ray tracing y excelente rendimiento en 1440p.',
                'brand' => 'NVIDIA',
                'barcode' => '812674024556',
                'mpn' => '900-1G141-2518-000',
            ],
            [
                'category_slug' => 'componentes-de-pc',
                'name' => 'Corsair Vengeance DDR5 32GB',
                'sku' => 'CORVENDDR532-001',
                'slug' => 'corsair-vengeance-ddr5-32gb',
                'short_description' => 'Memoria de alto rendimiento para PC',
                'description' => 'Kit de memoria DDR5 Corsair Vengeance de 32GB con disipador y perfil XMP.',
                'brand' => 'Corsair',
                'barcode' => '840006660871',
                'mpn' => 'CMK32GX5M2B6000C36',
            ],
            [
                'category_slug' => 'remeras-hombre',
                'name' => 'Remera Básica Premium Hombre Negra',
                'sku' => 'REMHNEG-001',
                'slug' => 'remera-basica-premium-hombre-negra',
                'short_description' => 'Remera de algodón peinado de corte regular',
                'description' => 'Remera básica premium confeccionada en algodón peinado, cómoda para uso diario y fácil de combinar.',
                'brand' => 'Levis',
                'barcode' => '193239831102',
                'mpn' => 'LBPH-NEG-REG',
            ],
            [
                'category_slug' => 'jeans-hombre',
                'name' => 'Jean Slim Fit Hombre Azul Oscuro',
                'sku' => 'JEANHSLIM-001',
                'slug' => 'jean-slim-fit-hombre-azul-oscuro',
                'short_description' => 'Denim flexible para uso urbano',
                'description' => 'Jean slim fit para hombre con lavado azul oscuro, elastano y terminación moderna.',
                'brand' => 'Wrangler',
                'barcode' => '194894223018',
                'mpn' => 'WR-SLIM-AD',
            ],
            [
                'category_slug' => 'camisas-hombre',
                'name' => 'Camisa Oxford Hombre Celeste',
                'sku' => 'CAMOXH-001',
                'slug' => 'camisa-oxford-hombre-celeste',
                'short_description' => 'Camisa clásica para oficina o eventos',
                'description' => 'Camisa Oxford de manga larga con cuello abotonado, ideal para looks formales y smart casual.',
                'brand' => 'Tommy Hilfiger',
                'barcode' => '196283110224',
                'mpn' => 'TH-OXF-CLST',
            ],
            [
                'category_slug' => 'vestidos-mujer',
                'name' => 'Vestido Midi Satinado Verde',
                'sku' => 'VESTMIDV-001',
                'slug' => 'vestido-midi-satinado-verde',
                'short_description' => 'Vestido elegante para eventos y salidas',
                'description' => 'Vestido midi satinado con caída fluida, tiras finas y silueta sofisticada.',
                'brand' => 'Zara',
                'barcode' => '8434487119802',
                'mpn' => 'ZR-VMSV-24',
            ],
            [
                'category_slug' => 'blusas-mujer',
                'name' => 'Blusa Mujer Manga Larga Blanca',
                'sku' => 'BLUMBL-001',
                'slug' => 'blusa-mujer-manga-larga-blanca',
                'short_description' => 'Prenda versátil para oficina y salidas',
                'description' => 'Blusa de tejido liviano con manga larga, cuello en V y calce relajado.',
                'brand' => 'Mango',
                'barcode' => '8447144682250',
                'mpn' => 'MN-BLMLB-24',
            ],
            [
                'category_slug' => 'jeans-mujer',
                'name' => 'Jean Mom Mujer Azul Claro',
                'sku' => 'JEANMM-001',
                'slug' => 'jean-mom-mujer-azul-claro',
                'short_description' => 'Cintura alta y fit relajado',
                'description' => 'Jean mom fit con denim suave, cintura alta y lavado azul claro.',
                'brand' => 'Levis',
                'barcode' => '197667110884',
                'mpn' => 'LV-MOM-ACL',
            ],
            [
                'category_slug' => 'zapatillas',
                'name' => 'Nike Air Max 270',
                'sku' => 'NIKEAIR270-001',
                'slug' => 'nike-air-max-270',
                'short_description' => 'Zapatilla urbana con gran amortiguación',
                'description' => 'Nike Air Max 270 con unidad Air visible, diseño moderno y gran comodidad para uso diario.',
                'brand' => 'Nike',
                'barcode' => '194499683120',
                'mpn' => 'AH8050-002',
            ],
            [
                'category_slug' => 'zapatillas',
                'name' => 'Adidas Ultraboost Light',
                'sku' => 'ADIULBL-001',
                'slug' => 'adidas-ultraboost-light',
                'short_description' => 'Running y confort premium en cada paso',
                'description' => 'Adidas Ultraboost Light con entresuela BOOST ligera y upper Primeknit.',
                'brand' => 'Adidas',
                'barcode' => '4066756584410',
                'mpn' => 'GZ5159',
            ],
            [
                'category_slug' => 'mochilas',
                'name' => 'Mochila Urbana Antirrobo 20L',
                'sku' => 'MOCHURB20-001',
                'slug' => 'mochila-urbana-antirrobo-20l',
                'short_description' => 'Ideal para notebook y traslados diarios',
                'description' => 'Mochila urbana con compartimento acolchado para notebook, cierres ocultos y puerto USB externo.',
                'brand' => 'Samsonite',
                'barcode' => '043202915503',
                'mpn' => 'SA-URB-20L',
            ],
            [
                'category_slug' => 'relojes',
                'name' => 'Casio G-Shock GA-2100',
                'sku' => 'CASIOGA2100-001',
                'slug' => 'casio-g-shock-ga-2100',
                'short_description' => 'Resistencia y estilo deportivo',
                'description' => 'Reloj Casio G-Shock GA-2100 con diseño octogonal, resistencia a impactos y 200 metros de resistencia al agua.',
                'brand' => 'Casio',
                'barcode' => '4549526241708',
                'mpn' => 'GA2100-1A1',
            ],
            [
                'category_slug' => 'lentes-de-sol',
                'name' => 'Ray-Ban Wayfarer Classic',
                'sku' => 'RBWAY-001',
                'slug' => 'ray-ban-wayfarer-classic',
                'short_description' => 'Ícono atemporal con protección UV',
                'description' => 'Lentes de sol Ray-Ban Wayfarer Classic con montura icónica y lentes de alta calidad.',
                'brand' => 'Ray-Ban',
                'barcode' => '8053672228471',
                'mpn' => 'RB2140-901',
            ],
            [
                'category_slug' => 'sofas-y-sillones',
                'name' => 'Sofá 3 Cuerpos Gris Oslo',
                'sku' => 'SOFAOSLO-001',
                'slug' => 'sofa-3-cuerpos-gris-oslo',
                'short_description' => 'Diseño contemporáneo para living',
                'description' => 'Sofá de 3 cuerpos tapizado en tela gris con estructura robusta y almohadones de alta densidad.',
                'brand' => 'Rosen',
                'barcode' => '7806510001409',
                'mpn' => 'OSLO-3C-GRIS',
            ],
            [
                'category_slug' => 'mesas',
                'name' => 'Mesa de Comedor Nórdica 160 cm',
                'sku' => 'MESANOR160-001',
                'slug' => 'mesa-comedor-nordica-160-cm',
                'short_description' => 'Mesa amplia con diseño escandinavo',
                'description' => 'Mesa de comedor de 160 cm con tapa de madera y patas estilo nórdico para espacios modernos.',
                'brand' => 'Nordic Home',
                'barcode' => '7798184960231',
                'mpn' => 'NH-MDC160',
            ],
            [
                'category_slug' => 'placares-y-roperos',
                'name' => 'Placard 6 Puertas Roble Claro',
                'sku' => 'PLAC6P-001',
                'slug' => 'placard-6-puertas-roble-claro',
                'short_description' => 'Amplio espacio de guardado para dormitorio',
                'description' => 'Placard de 6 puertas con barral, estantes internos y acabado roble claro.',
                'brand' => 'Orlandi',
                'barcode' => '7798373341025',
                'mpn' => 'OR-PLAC6-RC',
            ],
            [
                'category_slug' => 'heladeras',
                'name' => 'Heladera No Frost 420L Inverter',
                'sku' => 'HEL420INV-001',
                'slug' => 'heladera-no-frost-420l-inverter',
                'short_description' => 'Gran capacidad y eficiencia energética',
                'description' => 'Heladera no frost de 420 litros con tecnología inverter, freezer superior y estantes regulables.',
                'brand' => 'LG',
                'barcode' => '8806091211800',
                'mpn' => 'GT39BPP',
            ],
            [
                'category_slug' => 'lavarropas',
                'name' => 'Lavarropas Inverter 10.5 kg',
                'sku' => 'LAV105INV-001',
                'slug' => 'lavarropas-inverter-10-5kg',
                'short_description' => 'Carga frontal con múltiples programas',
                'description' => 'Lavarropas automático de 10.5 kg con motor inverter, vapor y programas para prendas delicadas.',
                'brand' => 'Samsung',
                'barcode' => '8806090601756',
                'mpn' => 'WW10T554DAW',
            ],
            [
                'category_slug' => 'pequenos-electrodomesticos',
                'name' => 'Cafetera Espresso Automática',
                'sku' => 'CAFESP-001',
                'slug' => 'cafetera-espresso-automatica',
                'short_description' => 'Espresso y cappuccino en casa',
                'description' => 'Cafetera espresso automática con espumador de leche, molienda ajustable y panel táctil.',
                'brand' => 'DeLonghi',
                'barcode' => '8004399335038',
                'mpn' => 'ECAM220.22.GB',
            ],
            [
                'category_slug' => 'muebles-de-jardin',
                'name' => 'Juego de Jardín 4 Piezas Ratán',
                'sku' => 'JGJARDIN4-001',
                'slug' => 'juego-jardin-4-piezas-ratan',
                'short_description' => 'Conjunto para exterior con mesa y sillones',
                'description' => 'Juego de jardín de 4 piezas símil ratán con almohadones y mesa de centro.',
                'brand' => 'Garden Life',
                'barcode' => '7798349871016',
                'mpn' => 'GL-RATAN-4P',
            ],
            [
                'category_slug' => 'parrillas',
                'name' => 'Parrilla a Gas 4 Quemadores',
                'sku' => 'PARRGAS4-001',
                'slug' => 'parrilla-gas-4-quemadores',
                'short_description' => 'Ideal para reuniones y quinchos',
                'description' => 'Parrilla a gas con 4 quemadores, tapa con termómetro y bandejas laterales.',
                'brand' => 'Char-Broil',
                'barcode' => '047362463417',
                'mpn' => '463377319',
            ],
            [
                'category_slug' => 'herramientas-de-jardin',
                'name' => 'Podadora Eléctrica 1400W',
                'sku' => 'POD1400W-001',
                'slug' => 'podadora-electrica-1400w',
                'short_description' => 'Corte prolijo para césped residencial',
                'description' => 'Podadora eléctrica compacta con altura regulable y bolsa recolectora.',
                'brand' => 'Black+Decker',
                'barcode' => '885911692781',
                'mpn' => 'BEMW461BH-AR',
            ],
            [
                'category_slug' => 'shampoo',
                'name' => 'Shampoo Reparación Intensa 400 ml',
                'sku' => 'SHAMPREP-001',
                'slug' => 'shampoo-reparacion-intensa-400ml',
                'short_description' => 'Nutrición para cabello dañado',
                'description' => 'Shampoo de reparación intensa con keratina y complejo nutritivo para uso frecuente.',
                'brand' => 'LOréal Paris',
                'barcode' => '7899706189902',
                'mpn' => 'ELVIVE-REP-400',
            ],
            [
                'category_slug' => 'acondicionadores',
                'name' => 'Acondicionador Hidratación Profunda 400 ml',
                'sku' => 'ACONHID-001',
                'slug' => 'acondicionador-hidratacion-profunda-400ml',
                'short_description' => 'Suavidad y brillo para cabello seco',
                'description' => 'Acondicionador con aceites nutritivos y acción desenredante para cabello seco.',
                'brand' => 'Pantene',
                'barcode' => '7501001164208',
                'mpn' => 'PANT-HID-400',
            ],
            [
                'category_slug' => 'proteccion-solar',
                'name' => 'Protector Solar FPS 50 Facial',
                'sku' => 'PROTSOL50-001',
                'slug' => 'protector-solar-fps-50-facial',
                'short_description' => 'Alta protección de uso diario',
                'description' => 'Protector solar facial FPS 50 con acabado seco y resistencia al agua.',
                'brand' => 'La Roche-Posay',
                'barcode' => '3337875797597',
                'mpn' => 'ANTHELIOS-UVMUNE',
            ],
            [
                'category_slug' => 'hidratantes',
                'name' => 'Crema Hidratante Facial con Ácido Hialurónico',
                'sku' => 'HIDFACAH-001',
                'slug' => 'crema-hidratante-facial-acido-hialuronico',
                'short_description' => 'Textura ligera para hidratación diaria',
                'description' => 'Crema facial hidratante con ácido hialurónico y ceramidas para fortalecer la barrera de la piel.',
                'brand' => 'CeraVe',
                'barcode' => '3337875597449',
                'mpn' => 'CV-PM-FACE',
            ],
            [
                'category_slug' => 'labiales',
                'name' => 'Labial Mate Larga Duración Rojo',
                'sku' => 'LABMATROJ-001',
                'slug' => 'labial-mate-larga-duracion-rojo',
                'short_description' => 'Color intenso con acabado mate',
                'description' => 'Labial líquido mate de larga duración con textura confortable y alta pigmentación.',
                'brand' => 'Maybelline',
                'barcode' => '3600531411190',
                'mpn' => 'SM-RED-20',
            ],
            [
                'category_slug' => 'mancuernas',
                'name' => 'Set de Mancuernas Ajustables 20 kg',
                'sku' => 'MANC20KG-001',
                'slug' => 'set-mancuernas-ajustables-20kg',
                'short_description' => 'Entrenamiento de fuerza en casa',
                'description' => 'Set de mancuernas ajustables con discos intercambiables y barras con agarre texturado.',
                'brand' => 'Everlast',
                'barcode' => '4710008032018',
                'mpn' => 'EV-DB20-SET',
            ],
            [
                'category_slug' => 'bancos-de-musculacion',
                'name' => 'Banco de Musculación Multifunción',
                'sku' => 'BANCMULTI-001',
                'slug' => 'banco-musculacion-multifuncion',
                'short_description' => 'Banco regulable para entrenamiento completo',
                'description' => 'Banco de musculación con inclinación ajustable, soporte para piernas y estructura reforzada.',
                'brand' => 'Athletic',
                'barcode' => '7798160625048',
                'mpn' => 'ATH-BM-REG',
            ],
            [
                'category_slug' => 'zapatillas-de-running',
                'name' => 'Asics Gel-Nimbus 26',
                'sku' => 'ASICSN26-001',
                'slug' => 'asics-gel-nimbus-26',
                'short_description' => 'Amortiguación premium para largas distancias',
                'description' => 'Zapatillas de running Asics Gel-Nimbus 26 con gran amortiguación y upper transpirable.',
                'brand' => 'Asics',
                'barcode' => '4550457311965',
                'mpn' => '1011B794-001',
            ],
            [
                'category_slug' => 'relojes-deportivos',
                'name' => 'Garmin Forerunner 255',
                'sku' => 'GARFR255-001',
                'slug' => 'garmin-forerunner-255',
                'short_description' => 'GPS preciso para running y triatlón',
                'description' => 'Reloj deportivo Garmin con métricas avanzadas, planes de entrenamiento y gran autonomía.',
                'brand' => 'Garmin',
                'barcode' => '753759278088',
                'mpn' => '010-02641-20',
            ],
            [
                'category_slug' => 'bicicletas',
                'name' => 'Bicicleta MTB Rodado 29 Aluminio',
                'sku' => 'BICI29MTB-001',
                'slug' => 'bicicleta-mtb-rodado-29-aluminio',
                'short_description' => 'Ideal para senderos y uso mixto',
                'description' => 'Mountain bike rodado 29 con cuadro de aluminio, suspensión delantera y frenos a disco.',
                'brand' => 'Venzo',
                'barcode' => '7798345201084',
                'mpn' => 'VZ-R29-ALU',
            ],
            [
                'category_slug' => 'cascos-ciclismo',
                'name' => 'Casco de Ciclismo Ruta Aero',
                'sku' => 'CASCORUTA-001',
                'slug' => 'casco-ciclismo-ruta-aero',
                'short_description' => 'Protección ligera con buena ventilación',
                'description' => 'Casco de ciclismo para ruta con construcción liviana, sistema de ajuste trasero y ventilación optimizada.',
                'brand' => 'Giro',
                'barcode' => '768686247271',
                'mpn' => 'GIRO-AGILIS',
            ],
            [
                'category_slug' => 'cochecitos',
                'name' => 'Cochecito Travel System City',
                'sku' => 'COCHCITY-001',
                'slug' => 'cochecito-travel-system-city',
                'short_description' => 'Paseo cómodo desde recién nacido',
                'description' => 'Cochecito travel system con huevito, reclinado múltiple y plegado compacto.',
                'brand' => 'Graco',
                'barcode' => '047406174199',
                'mpn' => 'TS-CITY-LX',
            ],
            [
                'category_slug' => 'mamaderas',
                'name' => 'Set de Mamaderas Anticólicos x3',
                'sku' => 'MAMX3-001',
                'slug' => 'set-mamaderas-anticolicos-x3',
                'short_description' => 'Flujo controlado para alimentación diaria',
                'description' => 'Set de mamaderas anticólicos con tetinas de silicona y válvula de ventilación.',
                'brand' => 'Philips Avent',
                'barcode' => '8710103879989',
                'mpn' => 'SCY903-03',
            ],
            [
                'category_slug' => 'cunas',
                'name' => 'Cuna Funcional Blanca con Cajonera',
                'sku' => 'CUNAFUNC-001',
                'slug' => 'cuna-funcional-blanca-cajonera',
                'short_description' => 'Cuna evolutiva con espacio de guardado',
                'description' => 'Cuna funcional con cajonera lateral, baranda desmontable y terminación lavable.',
                'brand' => 'La Valenziana',
                'barcode' => '7798384001253',
                'mpn' => 'LV-CUNA-FUN',
            ],
            [
                'category_slug' => 'arroz-y-legumbres',
                'name' => 'Arroz Largo Fino Premium 1 kg',
                'sku' => 'ARROZLF1-001',
                'slug' => 'arroz-largo-fino-premium-1kg',
                'short_description' => 'Ideal para uso diario y recetas variadas',
                'description' => 'Arroz largo fino premium seleccionado, de cocción pareja y grano suelto.',
                'brand' => 'Gallo',
                'barcode' => '7790070410219',
                'mpn' => 'GAL-ARLF-1K',
            ],
            [
                'category_slug' => 'pastas',
                'name' => 'Pasta Penne Rigate 500 g',
                'sku' => 'PENNE500-001',
                'slug' => 'pasta-penne-rigate-500g',
                'short_description' => 'Pasta seca de sémola de trigo duro',
                'description' => 'Penne rigate de cocción firme, ideal para salsas rojas, blancas o pesto.',
                'brand' => 'Barilla',
                'barcode' => '8076809523405',
                'mpn' => 'BAR-PENNE-500',
            ],
            [
                'category_slug' => 'aceites-y-vinagres',
                'name' => 'Aceite de Oliva Extra Virgen 500 ml',
                'sku' => 'OLIVA500-001',
                'slug' => 'aceite-oliva-extra-virgen-500ml',
                'short_description' => 'Sabor suave para cocina y ensaladas',
                'description' => 'Aceite de oliva extra virgen de primera prensada con perfil suave y afrutado.',
                'brand' => 'Natura',
                'barcode' => '7790272002144',
                'mpn' => 'NAT-AEVO-500',
            ],
            [
                'category_slug' => 'gaseosas',
                'name' => 'Gaseosa Cola 2.25 L',
                'sku' => 'COLA225-001',
                'slug' => 'gaseosa-cola-2-25l',
                'short_description' => 'Bebida gaseosa clásica para compartir',
                'description' => 'Gaseosa sabor cola de 2.25 litros, ideal para reuniones y consumo familiar.',
                'brand' => 'Coca-Cola',
                'barcode' => '7790895001040',
                'mpn' => 'CC-COLA-225',
            ],
            [
                'category_slug' => 'aguas',
                'name' => 'Agua Mineral sin Gas 1.5 L',
                'sku' => 'AGUA15-001',
                'slug' => 'agua-mineral-sin-gas-1-5l',
                'short_description' => 'Hidratación diaria de mineralización balanceada',
                'description' => 'Agua mineral natural sin gas en botella PET de 1.5 litros.',
                'brand' => 'Villavicencio',
                'barcode' => '7790315002285',
                'mpn' => 'VILLA-AG15',
            ],
            [
                'category_slug' => 'cervezas-y-vinos',
                'name' => 'Vino Malbec Reserva 750 ml',
                'sku' => 'MALBEC750-001',
                'slug' => 'vino-malbec-reserva-750ml',
                'short_description' => 'Tinto equilibrado con crianza parcial',
                'description' => 'Vino Malbec reserva de 750 ml con notas a frutos rojos, vainilla y buena estructura.',
                'brand' => 'Trapiche',
                'barcode' => '7790240018092',
                'mpn' => 'TRP-MAL-RSV',
            ],
            [
                'category_slug' => 'detergentes',
                'name' => 'Detergente Concentrado Limón 750 ml',
                'sku' => 'DET750-001',
                'slug' => 'detergente-concentrado-limon-750ml',
                'short_description' => 'Poder desengrasante para vajilla',
                'description' => 'Detergente concentrado con fragancia limón y alto poder desengrasante.',
                'brand' => 'Magistral',
                'barcode' => '7791290012457',
                'mpn' => 'MAG-LIM-750',
            ],
            [
                'category_slug' => 'desinfectantes',
                'name' => 'Desinfectante Multiuso Lavanda 900 ml',
                'sku' => 'DESLAV900-001',
                'slug' => 'desinfectante-multiuso-lavanda-900ml',
                'short_description' => 'Limpieza diaria con fragancia fresca',
                'description' => 'Desinfectante multiuso para pisos y superficies con fragancia lavanda.',
                'brand' => 'Ayudín',
                'barcode' => '7790520019105',
                'mpn' => 'AYU-LAV-900',
            ],
            [
                'category_slug' => 'lavado-de-ropa',
                'name' => 'Jabón Líquido para Ropa 3 L',
                'sku' => 'JABROP3L-001',
                'slug' => 'jabon-liquido-ropa-3l',
                'short_description' => 'Limpieza profunda para ropa diaria',
                'description' => 'Jabón líquido para ropa de alto rendimiento, apto para lavarropas automáticos.',
                'brand' => 'Skip',
                'barcode' => '7791293044301',
                'mpn' => 'SKIP-LIQ-3L',
            ],
        ];
    }

    private function createRelatedRecords(Product $product): void
    {
        $product->loadMissing('category.parent.parent');

        ProductDetail::updateOrCreate(
            ['product_id' => $product->id],
            $this->productDetailAttributes($product)
        );

        ProductWeb::updateOrCreate(
            ['product_id' => $product->id],
            $this->productWebAttributes($product)
        );

        ProductGoogle::updateOrCreate(
            ['product_id' => $product->id],
            $this->productGoogleAttributes($product)
        );

        ProductMercadolibre::updateOrCreate(
            ['product_id' => $product->id],
            $this->productMercadolibreAttributes($product)
        );

        ProductMeta::updateOrCreate(
            ['product_id' => $product->id],
            $this->productMetaAttributes($product)
        );
    }

    private function productDetailAttributes(Product $product): array
    {
        $isFashion = $this->isFashionProduct($product);
        $isKids = $this->categoryPathContains($product, ['ninos', 'ninas', 'bebes']);
        $isElectronics = $this->categoryPathContains($product, ['iphone', 'smartphones', 'laptops', 'tablets', 'electronica']);
        $color = $this->derivedColor($product);
        $size = $this->derivedSize($product);
        $material = $this->derivedMaterial($product, $isFashion, $isElectronics);

        return [
            'product_id' => $product->id,
            'color' => $color,
            'size' => $size,
            'size_type' => $size ? 'regular' : null,
            'size_system' => $size ? 'US' : null,
            'material' => $material,
            'pattern' => $isFashion ? fake()->randomElement(['solid', 'striped', 'plaid']) : 'solid',
            'gender' => $this->categoryPathContains($product, ['hombre'])
                ? 'male'
                : ($this->categoryPathContains($product, ['mujer']) ? 'female' : ($isFashion ? 'unisex' : null)),
            'age_group' => $isKids ? 'kids' : ($isFashion ? 'adult' : null),
            'product_length' => fake()->optional(0.8)->randomFloat(2, 10, 45),
            'product_width' => fake()->optional(0.8)->randomFloat(2, 5, 35),
            'product_height' => fake()->optional(0.8)->randomFloat(2, 1, 10),
            'product_weight' => fake()->optional(0.8)->randomFloat(2, 0.2, 8),
            'product_dimension_unit' => 'cm',
            'product_weight_unit' => 'kg',
            'product_details' => [
                ['section' => 'General', 'attribute_name' => 'Brand', 'attribute_value' => $product->brand ?? 'Generic'],
                ['section' => 'General', 'attribute_name' => 'SKU', 'attribute_value' => $product->sku],
                ['section' => 'General', 'attribute_name' => 'Condition', 'attribute_value' => $product->condition],
                ['section' => 'General', 'attribute_name' => 'Category', 'attribute_value' => $this->categoryBreadcrumb($product)],
                ['section' => 'General', 'attribute_name' => 'Material', 'attribute_value' => $material ?? 'mixed'],
                ['section' => 'Media', 'attribute_name' => 'Main image', 'attribute_value' => $product->image_link],
            ],
            'product_highlights' => array_values(array_filter([
                $product->short_description,
                $product->brand ? 'Marca ' . $product->brand : null,
                $product->mpn ? 'MPN ' . $product->mpn : null,
                $color ? 'Color ' . ucfirst($color) : null,
            ])),
            'custom_label_0' => $product->brand,
            'custom_label_1' => $product->category?->name,
            'custom_label_2' => $product->condition,
            'custom_label_3' => $product->category?->parent?->name,
            'custom_label_4' => $product->category?->parent?->parent?->name,
            'is_adult' => false,
            'is_bundle' => false,
        ];
    }

    private function productWebAttributes(Product $product): array
    {
        $hasSale = fake()->boolean(35);

        return [
            'product_id' => $product->id,
            'is_active' => true,
            'status' => 'active',
            'link' => $this->productUrl($product),
            'product_type' => $this->categoryBreadcrumb($product),
            'sale_price' => $hasSale ? round($this->basePrice($product) * 0.92, 2) : null,
            'sale_price_start' => $hasSale ? now() : null,
            'sale_price_end' => $hasSale ? now()->addDays(14) : null,
            'item_group_id' => $product->slug,
        ];
    }

    private function productGoogleAttributes(Product $product): array
    {
        $price = $this->basePrice($product);
        $hasSale = fake()->boolean(30);

        return [
            'product_id' => $product->id,
            'is_active' => true,
            'status' => 'active',
            'google_id' => 'online:es:AR:' . $product->sku,
            'google_url' => $this->productUrl($product),
            'last_synced_at' => now(),
            'sync_errors' => null,
            'title' => $product->name,
            'description' => $product->description ?: $product->short_description,
            'link' => $this->productUrl($product),
            'price' => $price,
            'currency' => 'ARS',
            'availability' => $this->googleAvailability($product),
            'google_product_category' => $this->categoryBreadcrumb($product),
            'product_type' => $this->categoryBreadcrumb($product),
            'sale_price' => $hasSale ? round($price * 0.9, 2) : null,
            'sale_price_start' => $hasSale ? now() : null,
            'sale_price_end' => $hasSale ? now()->addDays(10) : null,
            'item_group_id' => $product->slug,
        ];
    }

    private function productMercadolibreAttributes(Product $product): array
    {
        $color = $this->derivedColor($product);
        $size = $this->derivedSize($product);

        return [
            'product_id' => $product->id,
            'is_active' => true,
            'status' => 'active',
            'ml_id' => 'MLA' . str_pad((string) $product->id, 9, '0', STR_PAD_LEFT),
            'ml_url' => $this->productUrl($product),
            'last_synced_at' => now(),
            'sync_errors' => null,
            'title' => $product->name,
            'category_id' => 'MLA' . str_pad((string) $product->category_id, 4, '0', STR_PAD_LEFT),
            'price' => $this->basePrice($product),
            'currency_id' => 'ARS',
            'available_quantity' => fake()->numberBetween(5, 40),
            'buying_mode' => 'buy_it_now',
            'listing_type_id' => 'gold_special',
            'attributes' => array_values(array_filter([
                ['id' => 'TITLE', 'value_name' => $product->name],
                $product->brand ? ['id' => 'BRAND', 'value_name' => $product->brand] : null,
                $product->barcode ? ['id' => 'GTIN', 'value_name' => $product->barcode] : null,
                $product->mpn ? ['id' => 'MPN', 'value_name' => $product->mpn] : null,
                $color ? ['id' => 'COLOR', 'value_name' => ucfirst($color)] : null,
                $size ? ['id' => 'SIZE', 'value_name' => $size] : null,
                ['id' => 'ITEM_CONDITION', 'value_name' => $product->condition],
            ])),
            'sale_terms' => [
                ['id' => 'WARRANTY_TYPE', 'value_name' => 'Garantía del vendedor'],
                ['id' => 'WARRANTY_TIME', 'value_name' => '12 meses'],
            ],
            'shipping' => [
                'mode' => 'me2',
                'local_pick_up' => false,
                'free_shipping' => $this->basePrice($product) >= 299,
            ],
            'warranty' => fake()->randomElement(['6 meses', '12 meses', 'Garantía oficial']),
            'variations' => $this->isFashionProduct($product) ? [
                [
                    'attribute_combinations' => array_values(array_filter([
                        $color ? ['id' => 'COLOR', 'value_name' => ucfirst($color)] : null,
                        $size ? ['id' => 'SIZE', 'value_name' => $size] : null,
                    ])),
                    'price' => $this->basePrice($product),
                    'available_quantity' => fake()->numberBetween(3, 12),
                    'picture_ids' => [],
                ],
            ] : null,
        ];
    }

    private function productMetaAttributes(Product $product): array
    {
        $price = $this->basePrice($product);
        $hasSale = fake()->boolean(25);
        $color = $this->derivedColor($product);
        $size = $this->derivedSize($product);

        return [
            'product_id' => $product->id,
            'is_active' => true,
            'status' => 'active',
            'meta_id' => 'meta_' . strtolower($product->sku),
            'permalink' => $this->productUrl($product),
            'last_synced_at' => now(),
            'sync_errors' => null,
            'title' => $product->name,
            'description' => $product->description ?: $product->short_description,
            'availability' => $this->metaAvailability($product),
            'condition' => $product->condition,
            'price' => number_format($price, 2, '.', '') . ' ARS',
            'link' => $this->productUrl($product),
            'item_group_id' => $product->slug,
            'color' => $color,
            'size' => $size,
            'google_product_category' => $this->categoryBreadcrumb($product),
            'product_type' => $this->categoryBreadcrumb($product),
            'sale_price' => $hasSale ? round($price * 0.9, 2) : null,
            'sale_price_start' => $hasSale ? now() : null,
            'sale_price_end' => $hasSale ? now()->addDays(7) : null,
        ];
    }

    private function derivedColor(Product $product): ?string
    {
        return match (true) {
            $this->categoryPathContains($product, ['iphone', 'smartphones', 'laptops', 'tablets', 'smart-tv', 'audio']) => fake()->randomElement(['black', 'white', 'silver', 'blue', 'gray']),
            $this->isFashionProduct($product) => fake()->randomElement(['black', 'white', 'blue', 'green', 'beige']),
            $this->categoryPathContains($product, ['sofas-y-sillones', 'mesas', 'placares-y-roperos']) => fake()->randomElement(['gray', 'brown', 'oak', 'white']),
            default => null,
        };
    }

    private function derivedSize(Product $product): ?string
    {
        return match (true) {
            $this->isFashionProduct($product) => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            $this->categoryPathContains($product, ['mochilas', 'lentes-de-sol', 'relojes']) => 'one size',
            default => null,
        };
    }

    private function derivedMaterial(Product $product, bool $isFashion, bool $isElectronics): ?string
    {
        return match (true) {
            $isElectronics => fake()->randomElement(['aluminum', 'glass', 'titanium']),
            $isFashion => fake()->randomElement(['Cotton', 'Polyester', 'Leather']),
            $this->categoryPathContains($product, ['muebles', 'mesas', 'placares']) => fake()->randomElement(['wood', 'mdf', 'metal']),
            default => 'mixed',
        };
    }

    private function productUrl(Product $product): string
    {
        return rtrim((string) config('app.url', 'https://example.com'), '/') . '/products/' . $product->slug;
    }

    private function categoryBreadcrumb(Product $product): string
    {
        $segments = [];
        $category = $product->category;

        while ($category) {
            array_unshift($segments, $category->name);
            $category = $category->parent;
        }

        return implode(' > ', $segments);
    }

    private function basePrice(Product $product): float
    {
        $path = strtolower($this->categoryBreadcrumb($product));

        return match (true) {
            str_contains($path, 'iphone') => 1899.00,
            str_contains($path, 'smartphones') => 1499.00,
            str_contains($path, 'laptops') => 2499.00,
            str_contains($path, 'tablets') => 999.00,
            str_contains($path, 'audio') => 299.00,
            str_contains($path, 'gaming') => 799.00,
            str_contains($path, 'ropa'), str_contains($path, 'calzado') => 79.00,
            default => 199.00,
        };
    }

    private function googleAvailability(Product $product): string
    {
        return $product->availability_date && $product->availability_date->isFuture()
            ? 'preorder'
            : 'in_stock';
    }

    private function metaAvailability(Product $product): string
    {
        return $product->availability_date && $product->availability_date->isFuture()
            ? 'preorder'
            : 'in stock';
    }

    private function isFashionProduct(Product $product): bool
    {
        return $this->categoryPathContains($product, ['ropa', 'mujer', 'hombre', 'ninos', 'ninas', 'calzado']);
    }

    private function categoryPathContains(Product $product, array $needles): bool
    {
        $path = strtolower($this->categoryBreadcrumb($product));

        foreach ($needles as $needle) {
            if (str_contains($path, $needle)) {
                return true;
            }
        }

        return false;
    }
}
