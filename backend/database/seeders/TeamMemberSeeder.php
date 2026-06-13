<?php
namespace Database\Seeders;
use App\Models\TeamMember;
use Illuminate\Database\Seeder;
class TeamMemberSeeder extends Seeder {
    public function run(): void {
        $team = [
            ['name'=>'Arief Hidayat','role'=>'Founder & Lead Guide','specialization'=>'Mount Rinjani · Cultural Expeditions','years_experience'=>12,'origin'=>'Senaru, North Lombok','languages'=>'Indonesian, English, Basic French','certifications'=>'Certified Wilderness First Responder · National Guide License','bio'=>'Arief was born in the shadow of Rinjani and has summited the volcano over 400 times. He founded PT Lombok Nature Culture with one belief: the best travel happens when a guest is treated as a friend, not a customer.','is_active'=>true,'sort_order'=>1],
            ['name'=>'Dewi Sasak','role'=>'Cultural Experience Director','specialization'=>'Sasak Heritage · Village Ceremonies','years_experience'=>8,'origin'=>'Sade Village, Central Lombok','languages'=>'Indonesian, English, Sasak','certifications'=>'Certified Cultural Tourism Guide','bio'=>"Dewi grew up in Sade — one of Lombok's most traditional villages. Her deep family connections give guests access to ceremonies and workshops no commercial tour can replicate.",'is_active'=>true,'sort_order'=>2],
            ['name'=>'Bayu Pratama','role'=>'Senior Trek Guide','specialization'=>'Alpine Routes · Rinjani · Wilderness','years_experience'=>10,'origin'=>'Sembalun, East Lombok','languages'=>'Indonesian, English','certifications'=>'Mountain Rescue Certificate · Advanced First Aid · SAR Team Member','bio'=>'Bayu is part of the official Rinjani Search and Rescue team. His encyclopaedic knowledge of the terrain makes even the most demanding summit feel achievable.','is_active'=>true,'sort_order'=>3],
            ['name'=>'Sari Wulandari','role'=>'Honeymoon & Luxury Specialist','specialization'=>'Private Experiences · Luxury Planning','years_experience'=>6,'origin'=>'Mataram, West Lombok','languages'=>'Indonesian, English, Basic Japanese','certifications'=>'Hospitality Management · Luxury Travel Specialist (ILTM)','bio'=>'Sari coordinates every honeymoon and premium experience with the precision of a fine hotel concierge — from villa selection to romantic surprise setups.','is_active'=>true,'sort_order'=>4],
        ];
        foreach ($team as $m) { TeamMember::updateOrCreate(['name'=>$m['name']], $m); }
    }
}
