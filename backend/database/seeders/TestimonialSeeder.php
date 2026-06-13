<?php
namespace Database\Seeders;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
class TestimonialSeeder extends Seeder {
    public function run(): void {
        $testimonials = [
            ['quote'=>'Every detail exceeded our expectations. The Rinjani summit at sunrise will stay with us forever. LNC made it feel effortless — and deeply personal.','guest_name'=>'James & Emma Thornton','guest_origin'=>'London, United Kingdom','experience'=>'LNC-13 Grand Lombok Odyssey · 10 Days','rating'=>5,'is_active'=>true,'sort_order'=>1],
            ['quote'=>'Our honeymoon was beyond anything we imagined. Private villa, private guide, private moments. We felt like the only people on the island.','guest_name'=>'Sophie & Marc Dubois','guest_origin'=>'Lyon, France','experience'=>'LNC-04 Gili Meno Serenity · 4 Days','rating'=>5,'is_active'=>true,'sort_order'=>2],
            ['quote'=>'I travel often. This was the first time I truly switched off. The cultural programme was thoughtful, authentic — nothing touristy about it.','guest_name'=>'David Hartmann','guest_origin'=>'Berlin, Germany','experience'=>'LNC-02 Sasak Living Heritage · 4 Days','rating'=>5,'is_active'=>true,'sort_order'=>3],
        ];
        foreach ($testimonials as $t) { Testimonial::updateOrCreate(['guest_name'=>$t['guest_name']], $t); }
    }
}
