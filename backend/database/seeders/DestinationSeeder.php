<?php
namespace Database\Seeders;
use App\Models\Destination;
use Illuminate\Database\Seeder;
class DestinationSeeder extends Seeder {
    public function run(): void {
        $destinations = [
            ['name'=>'Kuta Mandalika','area'=>'South Lombok','color'=>'#2cb896','sort_order'=>1],
            ['name'=>'Senggigi & Barat','area'=>'West Lombok','color'=>'#c4964a','sort_order'=>2],
            ['name'=>'Gili Islands','area'=>'North Lombok','color'=>'#38a8d8','sort_order'=>3],
            ['name'=>'Highlands','area'=>'East & North Lombok','color'=>'#8b6f4e','sort_order'=>4],
        ];
        foreach ($destinations as $d) { Destination::updateOrCreate(['name'=>$d['name']], array_merge($d, ['is_active'=>true])); }
    }
}
