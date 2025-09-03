<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsWithImagesImport implements ToModel, WithHeadingRow
{
    protected $imagesFolder;

    public function __construct($imagesFolder = 'products_import_images')
    {
        // Folder inside storage/app/public where images are stored
        $this->imagesFolder = $imagesFolder;
    }

    public function model(array $row)
    {
        // Find category by name or ID if your Excel has category info
        $categoryId = null;
        if (!empty($row['category_id'])) {
            $categoryId = $row['category_id'];
        } elseif (!empty($row['category_name'])) {
            $category = Category::where('name', $row['category_name'])->first();
            if ($category) {
                $categoryId = $category->id;
            }
        }

        // Handle image file from folder
        $imagePath = null;
        if (!empty($row['image'])) {
            $imageFileName = trim($row['image']); // e.g. "product1.jpg"

            // Check if image exists in storage folder
          $publicImagePath = public_path('images/' . $imageFileName);
if (file_exists($publicImagePath)) {
    $imagePath = 'images/' . $imageFileName; // store relative path for blade asset()
}
        }

        return new Product([
            'name'             => $row['name'] ?? 'Unnamed product',
            'description'      => $row['description'] ?? null,
            'price'            => $row['price'] ?? 0,
            'stock'            => $row['stock'] ?? null,
            'brand'            => $row['brand'] ?? null,
            'discount_price'   => $row['discount_price'] ?? null,
            'meta_title'       => $row['meta_title'] ?? null,
            'meta_description' => $row['meta_description'] ?? null,
            'category_id'      => $categoryId,
            'is_active'        => isset($row['is_active']) ? (bool)$row['is_active'] : true,
            'image'            => $imagePath,
        ]);
    }
}
