<?php
namespace Database\Seeders;
use App\Models\Hotel;
use App\Models\HotelProperty;
use Illuminate\Database\Seeder;
class HotelSeeder extends Seeder {
    public function run(): void {
        $zones = [
            ['zone'=>'Kuta Mandalika','area'=>'South Lombok','zone_color'=>'#2cb896','sort_order'=>1,'properties'=>[
                ['image_path'=>'/uploads/hotels/hotel-sikara-lombok.jpg','name'=>'Sikara Lombok','type'=>'Boutique Hotel','room_type'=>'The Prime (Terrace)','features'=>'Balcony, garden/pool view, bathtub','price_low'=>'950.000','price_high'=>'1.200.000','breakfast'=>'Include (2 pax)','rating'=>'9.0/10 Booking.com','review_text'=>'"Oasis of peace in the middle of Kuta... The bathroom is impressive."','contact'=>'62 811-3900-899','sort_order'=>1],
                ['image_path'=>'/uploads/hotels/hotel-jivana-resort.jpg','name'=>'Jivana Resort','type'=>'Resort','room_type'=>'Poolside Suite','features'=>'Direct pool access, private terrace, bathtub','price_low'=>'1.600.000','price_high'=>'1.900.000','breakfast'=>'Include (2 pax)','rating'=>'4.7 Traveloka','review_text'=>'"Perfect environment. Very relaxed. Friendly staff & lots of koi."','contact'=>'62 819-0700-8889','sort_order'=>2],
                ['image_path'=>'/uploads/hotels/hotel-kalea-villas.jpg','name'=>'Kaleana Villas','type'=>'Bamboo Villa','room_type'=>'1-BR Bamboo Villa','features'=>'Private plunge pool, bamboo concept, open-air living','price_low'=>'2.200.000','price_high'=>'2.800.000','breakfast'=>'Include (floating breakfast)','rating'=>'9.4/10 Agoda','review_text'=>'"Amazing stylish spacious villa... located in a quiet area."','contact'=>'62 878-6588-8882','sort_order'=>3],
                ['image_path'=>'/uploads/hotels/hotel-origin-lombok.jpg','name'=>'Origin Lombok','type'=>'Boutique Hotel','room_type'=>'Garden Deluxe','features'=>'Yoga garden terrace, peaceful','price_low'=>'850.000','price_high'=>'1.100.000','breakfast'=>'Include (healthy)','rating'=>'4.6 Google','review_text'=>'"Great place for yoga and relaxation. Very peaceful atmosphere."','contact'=>'62 819-1744-8888','sort_order'=>4],
            ]],
            ['zone'=>'Senggigi & Barat','area'=>'West Lombok','zone_color'=>'#c4964a','sort_order'=>2,'properties'=>[
                ['name'=>'Sudamala Resort','type'=>'Resort','room_type'=>'Lingsar Garden Suite','features'=>'Garden terrace, semi-outdoor luxury bathroom','price_low'=>'1.700.000','price_high'=>'2.200.000','breakfast'=>'Include (à la carte)','rating'=>'9.2/10 Agoda','review_text'=>'"Met the most friendly staff who provide exceptional service."','contact'=>'62 812-3844-8000','sort_order'=>1],
                ['name'=>'Katamaran Resort','type'=>'Luxury Resort','room_type'=>'Ocean View Suite','features'=>'Full glass ocean view, bathtub with sea view','price_low'=>'2.500.000','price_high'=>'3.200.000','breakfast'=>'Include (buffet)','rating'=>'9.0/10 Booking.com','review_text'=>'"The view from the room spectacular... The pool is stunning."','contact'=>'62 819-0700-8000','sort_order'=>2],
                ['name'=>'Jeeva Klui','type'=>'Boutique Resort','room_type'=>'Ananda Segara','features'=>'Oceanfront, private terrace, daybed, bathtub','price_low'=>'2.800.000','price_high'=>'3.500.000','breakfast'=>'Include (premium)','rating'=>'4.7 Google','review_text'=>'"Sustainable luxury at its best. Very peaceful and cultural."','contact'=>'62 812-3700-9900','sort_order'=>3],
            ]],
            ['zone'=>'Gili Islands','area'=>'North Lombok','zone_color'=>'#38a8d8','sort_order'=>3,'properties'=>[
                ['name'=>'MAHAMAYA Gili Meno','type'=>'Eco Beach Resort','room_type'=>'Beachfront Villa','features'=>'Direct beach access, private terrace, sunset view','price_low'=>'2.200.000','price_high'=>'2.800.000','breakfast'=>'Include (à la carte)','rating'=>'4.6 Agoda','review_text'=>'"Eco-friendly resort with great food and stunning sunset location."','contact'=>'62 817-0333-0000','sort_order'=>1],
                ['name'=>'Pearl of Trawangan','type'=>'Bamboo Cottage','room_type'=>'Lumbung Cottage','features'=>'Unique bamboo architecture, semi-outdoor bathroom','price_low'=>'1.500.000','price_high'=>'2.000.000','breakfast'=>'Include (buffet)','rating'=>'9.5/10 Booking.com','review_text'=>'"Iconic bamboo architecture, great location but quiet."','contact'=>'62 811-3900-200','sort_order'=>2],
            ]],
            ['zone'=>'Highlands','area'=>'East & North Lombok','zone_color'=>'#8b6f4e','sort_order'=>4,'properties'=>[
                ['name'=>'Les Rizieres Tetebatu','type'=>'Eco Homestay','room_type'=>'Rice Field View','features'=>'Rinjani view, private terrace, no AC (naturally cool)','price_low'=>'600.000','price_high'=>'750.000','breakfast'=>'Include (homemade)','rating'=>'4.8 Orbitz','review_text'=>'"Spacious room in restored tobacco oven... stunning rice field views."','contact'=>'62 819-1700-5000','sort_order'=>1],
                ['name'=>'Rinjani Lodge','type'=>'Mountain Lodge','room_type'=>'Mountain View','features'=>'Infinity pool with Rinjani view, private terrace','price_low'=>'1.200.000','price_high'=>'1.500.000','breakfast'=>'Include (buffet)','rating'=>'4.6 Google','review_text'=>'"The infinity pool view is breathtaking. Worth the price."','contact'=>'62 819-0750-0000','sort_order'=>2],
            ]],
        ];
        foreach ($zones as $zoneData) {
            $properties = $zoneData['properties'];
            unset($zoneData['properties']);
            $hotel = Hotel::updateOrCreate(['zone' => $zoneData['zone']], array_merge($zoneData, ['is_active'=>true]));
            foreach ($properties as $prop) {
                HotelProperty::updateOrCreate(
                    ['hotel_id' => $hotel->id, 'name' => $prop['name']],
                    array_merge($prop, ['hotel_id' => $hotel->id, 'is_active' => true])
                );
            }
        }
    }
}
