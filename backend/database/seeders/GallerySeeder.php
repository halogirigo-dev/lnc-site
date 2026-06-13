<?php
namespace Database\Seeders;
use App\Models\Gallery;
use Illuminate\Database\Seeder;
class GallerySeeder extends Seeder {
    public function run(): void {
        $images = [
            ['image_path'=>'/uploads/gallery/BUCHSTEINERPHOTOGRAPHY-21.webp','alt_text'=>'Lombok Nature Culture Experience','category'=>'landscape','is_active'=>true,'sort_order'=>1],
            ['image_path'=>'/uploads/gallery/BUCHSTEINERPHOTOGRAPHY-32.webp','alt_text'=>'Lombok Nature Culture Experience','category'=>'landscape','is_active'=>true,'sort_order'=>2],
            ['image_path'=>'/uploads/gallery/BUCHSTEINERPHOTOGRAPHY-35.webp','alt_text'=>'Lombok Nature Culture Experience','category'=>'landscape','is_active'=>true,'sort_order'=>3],
            ['image_path'=>'/uploads/gallery/BUCHSTEINERPHOTOGRAPHY-42.webp','alt_text'=>'Lombok Nature Culture Experience','category'=>'landscape','is_active'=>true,'sort_order'=>4],
            ['image_path'=>'/uploads/gallery/BUCHSTEINERPHOTOGRAPHY-44.webp','alt_text'=>'Lombok Nature Culture Experience','category'=>'landscape','is_active'=>true,'sort_order'=>5],
        ];
        foreach ($images as $img) { Gallery::updateOrCreate(['image_path'=>$img['image_path']], $img); }
    }
}
