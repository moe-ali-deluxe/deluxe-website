<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run()
    {
        // Find the Surveyor category ID
        $surveyorCategory = Category::where('name', 'Surveyor')->first();

        if (!$surveyorCategory) {
            $surveyorCategory = Category::create([
                'name' => 'Surveyor',
                'slug' => Str::slug('Surveyor'),
            ]);
        }

        $products = [
            [
                'name' => 'Total Station Leica TS06',
                'description' => 'High-precision total station for surveying tasks.',
                'price' => 9500.00,
                'sku' => 'SURV-TS06',
                'stock' => 10,
                'image' => 'products/surveyor/total-station.jpg',
            ],
            [
                'name' => 'GPS RTK Receiver',
                'description' => 'Advanced GPS receiver with real-time kinematic accuracy.',
                'price' => 4200.00,
                'sku' => 'SURV-GPSRTK',
                'stock' => 15,
                'image' => 'products/surveyor/gps-rtk.jpg',
            ],
            [
                'name' => 'Automatic Level Nikon AC-2S',
                'description' => 'Reliable and precise automatic level for construction and surveying.',
                'price' => 350.00,
                'sku' => 'SURV-NIKAC2S',
                'stock' => 25,
                'image' => 'products/surveyor/auto-level.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::create([
                'category_id' => $surveyorCategory->id,
                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'description' => $product['description'],
                'price' => $product['price'],
                'sku' => $product['sku'],
                'stock' => $product['stock'],
                'image' => $product['image'],
            ]);
        }
    }
}
