<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        // Disable foreign key checks and truncate
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Parent category
        $dentalLab = Category::create(['name' => 'Dental Lab']);

        // Children
        $consumables = Category::create([
            'name' => 'Consumables',
            'parent_id' => $dentalLab->id,
        ]);

        $instrument = Category::create([
            'name' => 'Instrument',
            'parent_id' => $dentalLab->id,
        ]);

        $equipment = Category::create([
            'name' => 'Equipment',
            'parent_id' => $dentalLab->id,
        ]);

        // Sub-children for Consumables
        Category::create(['name' => 'Lab Consumables', 'parent_id' => $consumables->id]);
        Category::create(['name' => 'CAD CAM Materials', 'parent_id' => $consumables->id]);
        Category::create(['name' => 'Wax', 'parent_id' => $consumables->id]);

        // Sub-children for Instrument
        Category::create(['name' => 'Gauge Tips', 'parent_id' => $instrument->id]);
        Category::create(['name' => 'Calibers', 'parent_id' => $instrument->id]);
        Category::create(['name' => 'Mixing Bowls', 'parent_id' => $instrument->id]);

        // Sub-children for Equipment
        Category::create(['name' => 'Surveyor', 'parent_id' => $equipment->id]);
        Category::create(['name' => 'Face-Bow', 'parent_id' => $equipment->id]);
    
         // === Dental Clinic ===
        $dentalClinic = Category::create(['name' => 'Dental Clinic']);

        $disposables = Category::create(['name' => 'Disposables', 'parent_id' => $dentalClinic->id]);
        $clinicEquipment = Category::create(['name' => 'Equipment', 'parent_id' => $dentalClinic->id]);

        Category::create(['name' => 'Gloves', 'parent_id' => $disposables->id]);
        Category::create(['name' => 'Masks', 'parent_id' => $disposables->id]);

        Category::create(['name' => 'Chairs', 'parent_id' => $clinicEquipment->id]);
        Category::create(['name' => 'Lights', 'parent_id' => $clinicEquipment->id]);

        // === Dental Imaging ===
        $dentalImaging = Category::create(['name' => 'Dental Imaging']);

        $xrayFilms = Category::create(['name' => 'X-Ray Films', 'parent_id' => $dentalImaging->id]);
        $sensors = Category::create(['name' => 'Sensors', 'parent_id' => $dentalImaging->id]);

        Category::create(['name' => 'Periapical', 'parent_id' => $xrayFilms->id]);
        Category::create(['name' => 'Panoramic', 'parent_id' => $xrayFilms->id]);

        Category::create(['name' => 'Intraoral', 'parent_id' => $sensors->id]);
        Category::create(['name' => 'Extraoral', 'parent_id' => $sensors->id]);
    }
}
