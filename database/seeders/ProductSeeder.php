<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Kaos',
                'slug' => 'kaos',
                'type' => 'satuan',
                'category' => 'Apparel',
                'cost_price' => 75000,
                'selling_price' => 110000,
                'stock' => 150,
                'has_variants' => true,
                'variants' => ['S', 'M', 'L', 'XL', 'XXL'],
                'badge_text' => 'Official Tee',
                'icon' => 'shirt',
                'description' => 'Official T-Shirt SIPA Festival, Cotton Combed 24s sablon discharge tahan lama.',
                'is_active' => true,
            ],
            [
                'name' => 'Ganci',
                'slug' => 'ganci-1',
                'type' => 'satuan',
                'category' => 'Aksesoris',
                'cost_price' => 10000,
                'selling_price' => 15000,
                'stock' => 200,
                'has_variants' => false,
                'variants' => null,
                'badge_text' => null,
                'icon' => 'key',
                'description' => 'Gantungan Kunci Akrilik Official SIPA Varian Klasik.',
                'is_active' => true,
            ],
            [
                'name' => 'Ganci 2',
                'slug' => 'ganci-2',
                'type' => 'satuan',
                'category' => 'Aksesoris',
                'cost_price' => 14000,
                'selling_price' => 20000,
                'stock' => 180,
                'has_variants' => false,
                'variants' => null,
                'badge_text' => 'Double Side',
                'icon' => 'key-round',
                'description' => 'Gantungan Kunci Akrilik Premium Double Side dengan ring putar anti karat.',
                'is_active' => true,
            ],
            [
                'name' => 'Pin',
                'slug' => 'pin',
                'type' => 'satuan',
                'category' => 'Merchandise',
                'cost_price' => 1550,
                'selling_price' => 5000,
                'stock' => 350,
                'has_variants' => false,
                'variants' => null,
                'badge_text' => null,
                'icon' => 'badge-check',
                'description' => 'Pin Button Enamel Maskot SIPA Festival ukuran 44mm finishing doff.',
                'is_active' => true,
            ],
            [
                'name' => 'Stiker',
                'slug' => 'stiker',
                'type' => 'satuan',
                'category' => 'Merchandise',
                'cost_price' => 206,
                'selling_price' => 1000,
                'stock' => 800,
                'has_variants' => false,
                'variants' => null,
                'badge_text' => null,
                'icon' => 'sparkles',
                'description' => 'Stiker Vinyl Die Cut Anti Air & Tahan Goresan artwork SIPA.',
                'is_active' => true,
            ],
            [
                'name' => 'Bundling pin',
                'slug' => 'bundling-pin',
                'type' => 'bundling',
                'category' => 'Paket Bundling',
                'cost_price' => 5000,
                'selling_price' => 12000,
                'stock' => 90,
                'has_variants' => false,
                'variants' => null,
                'badge_text' => 'Hemat Rp 3.000',
                'icon' => 'package',
                'description' => 'Paket Hemat 3 pcs Pin Maskot Koleksi Festival berbagai desain.',
                'is_active' => true,
            ],
            [
                'name' => 'Bundling stiker',
                'slug' => 'bundling-stiker',
                'type' => 'bundling',
                'category' => 'Paket Bundling',
                'cost_price' => 618,
                'selling_price' => 5000,
                'stock' => 120,
                'has_variants' => false,
                'variants' => null,
                'badge_text' => 'Paket Komplit (6 Pcs)',
                'icon' => 'boxes',
                'description' => 'Set Bundling isi 6 stiker eksklusif edisi tema panggung festival.',
                'is_active' => true,
            ],
            [
                'name' => 'Stiker limited',
                'slug' => 'stiker-limited',
                'type' => 'limited',
                'category' => 'Limited Drop',
                'cost_price' => 640,
                'selling_price' => 5000,
                'stock' => 75,
                'has_variants' => false,
                'variants' => null,
                'badge_text' => 'Hologram Limited',
                'icon' => 'flame',
                'description' => 'Stiker Hologram Foil Rainbow Khusus Edisi Terbatas SIPA Merch.',
                'is_active' => true,
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
