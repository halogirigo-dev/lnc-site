<?php
namespace Database\Seeders;
use App\Models\Faq;
use Illuminate\Database\Seeder;
class FaqSeeder extends Seeder {
    public function run(): void {
        $faqs = [
            ['question'=>'How do I book a tour with Lombok Nature Culture?','answer'=>'Submit a journey request via our booking form or contact us on WhatsApp. We reply within 24 hours with a personalised itinerary. No payment is required at this stage.','category'=>'booking','sort_order'=>1],
            ['question'=>'Is accommodation included in the tour price?','answer'=>'No. Hotel is booked separately from our service packages. We partner with curated properties across 4 zones of Lombok and the Gili Islands, and we coordinate everything — you simply choose your comfort level.','category'=>'booking','sort_order'=>2],
            ['question'=>'Are tours private or shared group tours?','answer'=>'All experiences are 100% private. We never combine your group with other guests. Every itinerary is tailored exclusively for you.','category'=>'general','sort_order'=>3],
            ['question'=>'What languages do your guides speak?','answer'=>'Our guides speak Indonesian and English fluently. Arief (Founder) also speaks basic French, Dewi speaks Sasak, and Sari speaks basic Japanese.','category'=>'general','sort_order'=>4],
            ['question'=>'What is the cancellation policy?','answer'=>'Cancellations more than 30 days before departure receive a full refund minus the deposit. Cancellations within 14 days are non-refundable. Full details are on our Legal & Policies page.','category'=>'payment','sort_order'=>5],
            ['question'=>'When is the best time to visit Lombok?','answer'=>'The dry season (May–October) is ideal for Rinjani trekking and beach activities, with July–September being peak season. April–June and October–November offer great conditions with fewer crowds.','category'=>'logistics','sort_order'=>6],
        ];
        foreach ($faqs as $faq) { Faq::updateOrCreate(['question'=>$faq['question']], array_merge($faq, ['is_active'=>true])); }
    }
}
