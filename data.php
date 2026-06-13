<?php
// ─── ALL REAL CONTENT DATA ────────────────────────────────────
// Source: FIX DRAFT KATALOG.docx, Katalog Short/Long Stay, Hotel Database

// ── SERVICE STANDARDS (SOP) ──────────────────────────────────
$sop = [
  'Check-in First Policy'     => 'Guests are picked up at the airport and brought to the hotel first — to check in, drop luggage, and freshen up before any activity.',
  'In-Car Hospitality'        => 'Welcome drink (fresh young coconut / juice) served inside the vehicle on arrival from the airport.',
  'LNC Tumbler Gift'          => 'Exclusive LNC reusable tumbler provided at check-in — our zero-waste commitment.',
  'Tropical Refreshment'      => 'Fresh tropical fruit (pineapple/watermelon) served on a banana leaf at every water activity.',
  'Meals Policy'              => 'Lunch & Dinner are NOT included unless explicitly marked "INCLUDED" — guests choose freely. Our team recommends the best local spots.',
  'Hotel Policy'              => 'Package prices EXCLUDE accommodation. LNC provides curated hotel recommendations per zone. Book through us for seamless coordination.',
];

// ── SHORT STAY PACKAGES (3–5 Days) ───────────────────────────
$packages_short = [
  [
    'id'       => 'LNC-01',
    'title'    => 'Lombok Signature',
    'subtitle' => 'The Perfect Introduction to Sasak Culture & Secret Gilis',
    'duration' => '3 Days / 2 Nights',
    'category' => 'culture',
    'img'      => '/uploads/lombok-signature.jpg',
    'price'    => 3746600,
    'min_pax'  => 2,
    'includes' => ['Private car + fuel + driver', 'Private boat', 'Snorkeling gear', 'Trip documentation', 'Entrance tickets', 'Massage (reflexology)'],
    'excludes' => ['Hotel (2 nights)', 'Lunch & dinner'],
    'itinerary'=> [
      ['day'=>'Day 1','title'=>'The Heritage Trail','items'=>[
        'Airport pickup (LOP) — welcome coconut drink in car',
        'Hotel check-in & refresh',
        '14:00 — Desa Sukarara (traditional weaving & traditional dress photo)',
        '14:00 — Desa Sade/Ende (Sasak traditional houses)',
        '16:30 — Pantai Tanjung Aan (pepper-corn white sand)',
        '17:30 — Sunset Trekking Bukit Merese',
        '21:00 — Reflexology massage',
      ]],
      ['day'=>'Day 2','title'=>'The Secret Gilis','items'=>[
        '08:00 — Drive to Pelabuhan Tawun',
        '09:00 — Board private boat',
        '09:30 — Private snorkeling: Gili Nanggu (fruit & coconut water included)',
        '11:30 — Lunch at Gili Sudak seafood (personal expense)',
        '13:00 — Photo stop Gili Kedis',
        '15:30 — Return to hotel',
      ]],
      ['day'=>'Day 3','title'=>'City & Departure','items'=>[
        '09:00 — Souvenir shopping',
        '11:30 — Islamic Center NTB visit',
        '13:15 — Airport drop-off',
      ]],
    ],
  ],
  [
    'id'       => 'LNC-02',
    'title'    => 'The Sasak Living Heritage',
    'subtitle' => 'Immersive Cultural Experience & Highland Nature',
    'duration' => '4 Days / 3 Nights',
    'category' => 'culture',
    'img'      => '/uploads/sasak-living-heritage.jpg',
    'price'    => 3718000,
    'min_pax'  => 2,
    'includes' => ['Transport 4 days', 'Local guide', 'Cultural workshop', 'Massage'],
    'excludes' => ['Hotel (3 nights)', 'Lunch & dinner'],
    'itinerary'=> [
      ['day'=>'Day 1','title'=>'Arrival','items'=>[
        'Airport pickup — welcome drink in car',
        'Hotel check-in (Kuta Mandalika)',
        '14:00 — Desa Sukarara & Desa Sade',
        '17:30 — Sunset Trekking Bukit Merese',
        '21:00 — Massage',
      ]],
      ['day'=>'Day 2','title'=>'The Highlands (Tetebatu)','items'=>[
        '09:00 — Drive to Tetebatu',
        '11:00 — Lunch Begibung (Sasak menu, optional)',
        '12:30 — Artisan visit: coffee roasting & VCO coconut oil',
        '14:00 — Rice terrace trek (Rinjani view) & waterfall',
        '16:30 — Return to Kuta hotel',
      ]],
      ['day'=>'Day 3','title'=>'Free Day','items'=>[
        'Beach day: Tanjung Aan',
        'Optional: Sauna / Spa & Wellness',
      ]],
      ['day'=>'Day 4','title'=>'Departure','items'=>[
        '09:00 — Free time',
        '11:00 — Airport transfer',
      ]],
    ],
  ],
  [
    'id'       => 'LNC-03',
    'title'    => 'Mandalika Legends',
    'subtitle' => 'Legendary Myths & Pristine Southern Beaches',
    'duration' => '4 Days / 3 Nights',
    'category' => 'culture',
    'img'      => '/uploads/mandalika-legends.jpg',
    'price'    => 2566850,
    'min_pax'  => 2,
    'includes' => ['Transport', 'Entrance tickets'],
    'excludes' => ['Hotel', 'Lunch & dinner'],
    'itinerary'=> [
      ['day'=>'Day 1','title'=>'Check-in & Legend','items'=>[
        'Airport pickup — welcome drink',
        '14:30 — Hotel Kuta check-in',
        '15:30 — Pantai Seger (Princess Mandalika statue & Circuit view)',
        '16:30 — Sunset Bukit Seger + fresh fruit (LNC signature)',
      ]],
      ['day'=>'Day 2','title'=>'Hidden Beaches','items'=>[
        '09:00 — Gua Bangkang "Light of God"',
        '11:30 — Lunch at Pantai Selong Belanak (personal expense)',
        '15:30 — Relaxing at Selong Belanak',
      ]],
      ['day'=>'Day 3','title'=>'Free Day','items'=>['Tanjung Aan beach','Optional: Spa & wellness']],
      ['day'=>'Day 4','title'=>'Departure','items'=>['09:00 — Desa Banyumulek pottery village','10:30 — Airport transfer']],
    ],
  ],
  [
    'id'       => 'LNC-04',
    'title'    => 'Gili Meno Serenity',
    'subtitle' => 'Honeymoon Vibes, Turtles & Horse Riding',
    'duration' => '4 Days / 3 Nights',
    'category' => 'honeymoon',
    'img'      => '/uploads/gili-meno-serenity.jpg',
    'price'    => 5662800,
    'min_pax'  => 2,
    'includes' => ['Hotel Senggigi + Gili Meno (2 nights)', 'Speedboat return', 'Private car', 'Horse riding', 'Snorkeling boat', 'Romantic candle-light dinner'],
    'excludes' => ['Lunch (except dinner D2)', 'Personal expenses'],
    'itinerary'=> [
      ['day'=>'Day 1','title'=>'Arrival & Relax','items'=>[
        'Airport pickup — welcome drink',
        '12:00 — Drive to Senggigi',
        '14:00 — Hotel check-in Senggigi',
        '17:00 — Sunset at Bukit Malimbu',
      ]],
      ['day'=>'Day 2','title'=>'The Silent Island','items'=>[
        '09:00 — Speedboat to Gili Meno',
        '10:00 — Hotel check-in Meno',
        '10:30 — Snorkeling (statues & turtles)',
        '16:30 — Sunset horse riding on the beach',
        '19:30 — Romantic candle-light dinner (included)',
      ]],
      ['day'=>'Day 3','title'=>'Leisure Day','items'=>['Free time at Gili Meno','Explore beach, read, swim']],
      ['day'=>'Day 4','title'=>'Departure','items'=>['10:00 — Speedboat back to Lombok → Airport']],
    ],
  ],
  [
    'id'       => 'LNC-07',
    'title'    => 'Lombok Surf Retreat',
    'subtitle' => 'Master the Waves — Private Surf Coaching',
    'duration' => '3 Days / 2 Nights',
    'category' => 'adventure',
    'img'      => '/uploads/lombok-surf-retreat.jpg',
    'price'    => 0,
    'price_label' => 'Request Quote',
    'min_pax'  => 1,
    'includes' => ['Private car', 'Certified surf instructor', 'Board rental'],
    'excludes' => ['Hotel', 'Meals'],
    'itinerary'=> [
      ['day'=>'Day 1','title'=>'Arrival & First Waves','items'=>['Airport pickup','Hotel check-in Kuta','Afternoon: surf assessment & beginner session at Selong Belanak']],
      ['day'=>'Day 2','title'=>'Full Surf Day','items'=>['Morning session: technique coaching','Afternoon: free surf at Mawi or Are Guling','Evening: video review with instructor']],
      ['day'=>'Day 3','title'=>'Final Session & Departure','items'=>['Morning: final surf session','Transfer to airport']],
    ],
  ],
];

// ── LONG STAY PACKAGES (7–14 Days) ───────────────────────────
$packages_long = [
  [
    'id'       => 'LNC-13',
    'title'    => 'The Grand Lombok Odyssey',
    'subtitle' => 'The Ultimate All-in-One Lombok Experience',
    'duration' => '10 Days / 9 Nights',
    'category' => 'adventure',
    'price'    => 0,
    'price_label' => 'Request Quote',
    'min_pax'  => 2,
    'includes' => ['Accommodation 9 nights (3 zones)', 'Standby transport', 'Private boat', 'Full guide', '1× lunch Tetebatu', '1× farewell seafood dinner'],
    'excludes' => ['Most meals (personal expense)', 'International flights'],
    'itinerary'=> [
      ['day'=>'Day 1','title'=>'Arrival & Highland','items'=>['Airport → Desa Sukarara','16:00 — Drive to Tetebatu (East Lombok)','18:00 — Check-in Eco Homestay Tetebatu']],
      ['day'=>'Day 2','title'=>'Tetebatu Walking Tour','items'=>['09:00 — Rice terrace walk, monkey forest, small waterfall','12:00 — Traditional Sasak lunch (included)','13:30 — Coffee & spice plantation visit']],
      ['day'=>'Day 3','title'=>'Mountain Transfer','items'=>['09:00 — Drive to Sembalun','11:00 — Strawberry picking at local farm','13:00 — Pusuk Sembalun viewpoint','15:00 — Check-in Sembalun hotel (mountain view)']],
      ['day'=>'Day 4','title'=>'North Coast Journey','items'=>['08:30 — Sembalun farm tour','10:30 — Sendang Gile waterfall (Senaru)','14:30 — Speedboat to Gili Meno','15:00 — Check-in Gili Meno hotel']],
      ['day'=>'Day 5','title'=>'Gili Snorkeling','items'=>['09:30 — Private boat snorkeling (turtles & statues)','12:30 — Lunch at Gili Air (personal)','Free time afternoon']],
      ['day'=>'Day 6','title'=>'Gili Meno Leisure','items'=>['Morning: Salt Lake & turtle hatchery walk','Afternoon: Free','Sunset: west side of the island']],
      ['day'=>'Day 7','title'=>'Southbound','items'=>['10:00 — Speedboat to Lombok','Drive to Kuta Mandalika','14:00 — Hotel Kuta check-in']],
      ['day'=>'Day 8','title'=>'Surf & Sunset','items'=>['Morning: introductory surf at Selong Belanak','Afternoon: beach lunch','Sunset: Bukit Merese']],
      ['day'=>'Day 9','title'=>'Southern Coast','items'=>['09:00 — Tanjung Aan','11:30 — Desa Adat Sade','19:00 — Farewell seafood dinner (included)']],
      ['day'=>'Day 10','title'=>'Departure','items'=>['09:00 — Souvenir shopping → airport transfer']],
    ],
  ],
  [
    'id'       => 'LNC-14',
    'title'    => 'Slow Travel — Village & Sea',
    'subtitle' => 'Living Like a Local, Cooking & Traditional Fishing',
    'duration' => '7 Days / 6 Nights',
    'category' => 'culture',
    'price'    => 0,
    'price_label' => 'Request Quote',
    'min_pax'  => 2,
    'includes' => ['Transport', 'Local guide', 'Cooking class', 'Fishing session', 'Cultural activities'],
    'excludes' => ['Hotel', 'Most meals'],
    'itinerary'=> [
      ['day'=>'Day 1','title'=>'Village Arrival','items'=>['Airport pickup','Sasak village welcome ceremony','Afternoon: free exploration']],
      ['day'=>'Day 2','title'=>'Cultural Immersion','items'=>['Traditional cooking class with village elder','Weaving workshop (tenun ikat)','Evening: community storytelling']],
      ['day'=>'Day 3','title'=>'Fishing Day','items'=>['Early morning: traditional fishing with local fishermen','Afternoon: cook your catch','Evening: relaxation']],
      ['day'=>'Day 4','title'=>'Highland Trek','items'=>['Full day highland trail','Rice terrace walk','Coffee plantation visit']],
      ['day'=>'Day 5','title'=>'Sea & Snorkel','items'=>['Private boat snorkeling','Island picnic lunch','Beach relaxation']],
      ['day'=>'Day 6','title'=>'Free & Farewell','items'=>['Morning free','Afternoon: traditional market','Farewell dinner']],
      ['day'=>'Day 7','title'=>'Departure','items'=>['Airport transfer']],
    ],
  ],
];

// ── BALI PACKAGES (also offered) ─────────────────────────────
$packages_bali = [
  [
    'id'       => 'BALI-01',
    'title'    => 'Bali Honeymoon Journey',
    'subtitle' => 'Ubud · Munduk · Jimbaran — 16 Days Private Arrangement',
    'duration' => '16 Days / 15 Nights',
    'category' => 'honeymoon',
    'price'    => 0,
    'price_label' => 'Request Quote',
    'min_pax'  => 2,
    'includes' => ['Private car + driver standby 16 days', 'Licensed guide standby 16 days', 'SPA treatments (2× included)', 'VIP beach club access', 'Cooking class'],
    'excludes' => ['Flights', 'Hotel (3 locations)', 'Most meals'],
    'itinerary'=> [
      ['day'=>'Days 1–5','title'=>'Ubud — Culture & Serenity','items'=>['Mahajiva Private Pool Villa','Sacred Monkey Forest','Aloha Ubud Swing + Photographer','Tegalalang Rice Terrace','Tirta Empul Holy Spring','Signature Balinese Massage (90 min, included)']],
      ['day'=>'Days 6–10','title'=>'Munduk — Highland Escape','items'=>['Munduk Moding Plantation Nature Resort','Wanagiri Hidden Hill','Private canoeing at Lake Tamblingan','Banyumala Twin Waterfall trekking','Private cooking class (lunch included)','Traditional Boreh Bali treatment (90 min, included)']],
      ['day'=>'Days 11–16','title'=>'Jimbaran — Coastal Luxury','items'=>['Ahimsa Beach Villa Jimbaran','Uluwatu Cliff Temple','Kecak Fire Dance','Sundays Beach Club VIP access (F&B credit included)','Jimbaran seafood dinner']],
    ],
  ],
];

// ── HOTELS BY ZONE ────────────────────────────────────────────
$hotels = [
  [
    'zone'  => 'Kuta Mandalika',
    'area'  => 'South Lombok',
    'color' => '#2cb896',
    'properties' => [
      ['img'=>'/uploads/hotels/hotel-sikara-lombok.jpg','name'=>'Sikara Lombok','type'=>'Boutique Hotel','room'=>'The Prime (Terrace)','features'=>'Balcony, garden/pool view, bathtub','low'=>'950.000','high'=>'1.200.000','bf'=>'Include (2 pax)','rating'=>'9.0/10 Booking.com','review'=>'"Oasis of peace in the middle of Kuta... The bathroom is impressive."','contact'=>'62 811-3900-899'],
      ['img'=>'/uploads/hotels/hotel-jivana-resort.jpg','name'=>'Jivana Resort','type'=>'Resort','room'=>'Poolside Suite','features'=>'Direct pool access, private terrace, bathtub','low'=>'1.600.000','high'=>'1.900.000','bf'=>'Include (2 pax)','rating'=>'4.7 Traveloka','review'=>'"Perfect environment. Very relaxed. Friendly staff & lots of koi."','contact'=>'62 819-0700-8889'],
      ['img'=>'/uploads/hotels/hotel-kalea-villas.jpg','name'=>'Kaleana Villas','type'=>'Bamboo Villa','room'=>'1-BR Bamboo Villa','features'=>'Private plunge pool, bamboo concept, open-air living','low'=>'2.200.000','high'=>'2.800.000','bf'=>'Include (floating breakfast)','rating'=>'9.4/10 Agoda','review'=>'"Amazing stylish spacious villa... located in a quiet area."','contact'=>'62 878-6588-8882'],
      ['img'=>'/uploads/hotels/hotel-origin-lombok.jpg','name'=>'Origin Lombok','type'=>'Boutique Hotel','room'=>'Garden Deluxe','features'=>'Yoga garden terrace, peaceful','low'=>'850.000','high'=>'1.100.000','bf'=>'Include (healthy)','rating'=>'4.6 Google','review'=>'"Great place for yoga and relaxation. Very peaceful atmosphere."','contact'=>'62 819-1744-8888'],
    ],
  ],
  [
    'zone'  => 'Senggigi & Barat',
    'area'  => 'West Lombok',
    'color' => '#c4964a',
    'properties' => [
      ['name'=>'Sudamala Resort','type'=>'Resort','room'=>'Lingsar Garden Suite','features'=>'Garden terrace, semi-outdoor luxury bathroom','low'=>'1.700.000','high'=>'2.200.000','bf'=>'Include (à la carte)','rating'=>'9.2/10 Agoda','review'=>'"Met the most friendly staff who provide exceptional service."','contact'=>'62 812-3844-8000'],
      ['name'=>'Katamaran Resort','type'=>'Luxury Resort','room'=>'Ocean View Suite','features'=>'Full glass ocean view, bathtub with sea view','low'=>'2.500.000','high'=>'3.200.000','bf'=>'Include (buffet)','rating'=>'9.0/10 Booking.com','review'=>'"The view from the room spectacular... The pool is stunning."','contact'=>'62 819-0700-8000'],
      ['name'=>'Holiday Resort','type'=>'Beach Resort','room'=>'Beach Bungalow','features'=>'Beachfront, private terrace, large grounds','low'=>'1.200.000','high'=>'1.600.000','bf'=>'Include (buffet)','rating'=>'4.5 TripAdvisor','review'=>'"Great for families, huge pool and right on the beach."','contact'=>'62 811-3900-0888'],
      ['name'=>'Jeeva Klui','type'=>'Boutique Resort','room'=>'Ananda Segara','features'=>'Oceanfront, private terrace, daybed, bathtub','low'=>'2.800.000','high'=>'3.500.000','bf'=>'Include (premium)','rating'=>'4.7 Google','review'=>'"Sustainable luxury at its best. Very peaceful and cultural."','contact'=>'62 812-3700-9900'],
      ['name'=>'The Kayana','type'=>'Private Villa','room'=>'Duplex Pool Villa','features'=>'Large private pool, living room, total privacy','low'=>'3.000.000','high'=>'3.800.000','bf'=>'Include (floating)','rating'=>'9.5/10 Agoda','review'=>'"Amazing private pool villa, perfect for privacy."','contact'=>'62 819-xxx-xxx'],
    ],
  ],
  [
    'zone'  => 'Gili Islands',
    'area'  => 'North Lombok',
    'color' => '#38a8d8',
    'properties' => [
      ['name'=>'MAHAMAYA Gili Meno','type'=>'Eco Beach Resort','room'=>'Beachfront Villa','features'=>'Direct beach access, private terrace, sunset view','low'=>'2.200.000','high'=>'2.800.000','bf'=>'Include (à la carte)','rating'=>'4.6 Agoda','review'=>'"Eco-friendly resort with great food and stunning sunset location."','contact'=>'62 817-0333-0000'],
      ['name'=>'Seri Resort','type'=>'Garden Resort','room'=>'Garden Bungalow','features'=>'Garden terrace, near beach, clean white theme','low'=>'1.100.000','high'=>'1.400.000','bf'=>'Include (buffet)','rating'=>'4.5 TripAdvisor','review'=>'"Beautiful white theme resort, yoga deck is amazing."','contact'=>'62 819-0700-6600'],
      ['name'=>'Pearl of Trawangan','type'=>'Bamboo Cottage','room'=>'Lumbung Cottage','features'=>'Unique bamboo architecture, semi-outdoor bathroom','low'=>'1.500.000','high'=>'2.000.000','bf'=>'Include (buffet)','rating'=>'9.5/10 Booking.com','review'=>'"Iconic bamboo architecture, great location but quiet."','contact'=>'62 811-3900-200'],
      ['name'=>'Ponte Villas','type'=>'Pool Villa','room'=>'Private Pool Villa','features'=>'Private plunge pool, wooden aesthetic','low'=>'1.800.000','high'=>'2.400.000','bf'=>'Include (floating)','rating'=>'4.7 Google','review'=>'"Cute villas with private pools, very instagrammable."','contact'=>'62 819-0788-8000'],
    ],
  ],
  [
    'zone'  => 'Highlands',
    'area'  => 'East & North Lombok',
    'color' => '#8b6f4e',
    'properties' => [
      ['name'=>'Les Rizieres Tetebatu','type'=>'Eco Homestay','room'=>'Rice Field View','features'=>'Rinjani view, private terrace, no AC (naturally cool)','low'=>'600.000','high'=>'750.000','bf'=>'Include (homemade)','rating'=>'4.8 Orbitz','review'=>'"Spacious room in restored tobacco oven... stunning rice field views."','contact'=>'62 819-1700-5000'],
      ['name'=>'Nusantara Sembalun','type'=>'Mountain Hotel','room'=>'Superior Room','features'=>'Pergasingan Hill view terrace, hot water','low'=>'500.000','high'=>'650.000','bf'=>'Include (standard)','rating'=>'4.4 MakeMyTrip','review'=>'"Best view in Sembalun, directly facing Pergasingan Hill."','contact'=>'62 878-6500-4000'],
      ['name'=>'Rinjani Lodge','type'=>'Mountain Lodge','room'=>'Mountain View','features'=>'Infinity pool with Rinjani view, private terrace','low'=>'1.200.000','high'=>'1.500.000','bf'=>'Include (buffet)','rating'=>'4.6 Google','review'=>'"The infinity pool view is breathtaking. Worth the price."','contact'=>'62 819-0750-0000'],
    ],
  ],
];

// ── TEAM ─────────────────────────────────────────────────────
$team = [
  ['name'=>'Arief Hidayat','role'=>'Founder & Lead Guide','spec'=>'Mount Rinjani · Cultural Expeditions','years'=>12,'origin'=>'Senaru, North Lombok','lang'=>'Indonesian, English, Basic French','cert'=>'Certified Wilderness First Responder · National Guide License','bio'=>'Arief was born in the shadow of Rinjani and has summited the volcano over 400 times. He founded PT Lombok Nature Culture with one belief: the best travel happens when a guest is treated as a friend, not a customer.'],
  ['name'=>'Dewi Sasak','role'=>'Cultural Experience Director','spec'=>'Sasak Heritage · Village Ceremonies','years'=>8,'origin'=>'Sade Village, Central Lombok','lang'=>'Indonesian, English, Sasak','cert'=>'Certified Cultural Tourism Guide','bio'=>'Dewi grew up in Sade — one of Lombok\'s most traditional villages. Her deep family connections give guests access to ceremonies and workshops no commercial tour can replicate.'],
  ['name'=>'Bayu Pratama','role'=>'Senior Trek Guide','spec'=>'Alpine Routes · Rinjani · Wilderness','years'=>10,'origin'=>'Sembalun, East Lombok','lang'=>'Indonesian, English','cert'=>'Mountain Rescue Certificate · Advanced First Aid · SAR Team Member','bio'=>'Bayu is part of the official Rinjani Search and Rescue team. His encyclopaedic knowledge of the terrain makes even the most demanding summit feel achievable.'],
  ['name'=>'Sari Wulandari','role'=>'Honeymoon & Luxury Specialist','spec'=>'Private Experiences · Luxury Planning','years'=>6,'origin'=>'Mataram, West Lombok','lang'=>'Indonesian, English, Basic Japanese','cert'=>'Hospitality Management · Luxury Travel Specialist (ILTM)','bio'=>'Sari coordinates every honeymoon and premium experience with the precision of a fine hotel concierge — from villa selection to romantic surprise setups.'],
];

// ── TESTIMONIALS ─────────────────────────────────────────────
$testimonials = [
  ['quote'=>'Every detail exceeded our expectations. The Rinjani summit at sunrise will stay with us forever. LNC made it feel effortless — and deeply personal.','name'=>'James & Emma Thornton','origin'=>'London, United Kingdom','experience'=>'LNC-13 Grand Lombok Odyssey · 10 Days'],
  ['quote'=>'Our honeymoon was beyond anything we imagined. Private villa, private guide, private moments. We felt like the only people on the island.','name'=>'Sophie & Marc Dubois','origin'=>'Lyon, France','experience'=>'LNC-04 Gili Meno Serenity · 4 Days'],
  ['quote'=>'I travel often. This was the first time I truly switched off. The cultural programme was thoughtful, authentic — nothing touristy about it.','name'=>'David Hartmann','origin'=>'Berlin, Germany','experience'=>'LNC-02 Sasak Living Heritage · 4 Days'],
];

// ── EXPERIENCE CATEGORIES ─────────────────────────────────────
$categories = [
  ['id'=>'culture',   'label'=>'Culture & Heritage',  'icon'=>'◈','desc'=>'Sasak villages, weaving, temples, and living traditions'],
  ['id'=>'island',    'label'=>'Island Escape',        'icon'=>'◎','desc'=>'Gili Islands, snorkeling, sunset, and island life'],
  ['id'=>'adventure', 'label'=>'Adventure & Active',  'icon'=>'⛰','desc'=>'Surfing, trekking, fishing, and outdoor challenges'],
  ['id'=>'honeymoon', 'label'=>'Honeymoon & Romance', 'icon'=>'♡','desc'=>'Private villas, candlelit dinners, and intimate moments'],
  ['id'=>'long',      'label'=>'Long Stay (7–14 days)','icon'=>'◉','desc'=>'Full Lombok immersion and multi-zone journeys'],
];

// Helper: format IDR price
function fmt_idr($amount) {
  if (!$amount) return 'Request Quote';
  return 'Rp ' . number_format($amount, 0, ',', '.');
}
function fmt_usd($amount) {
  if (!$amount) return '';
  return '≈ USD $' . number_format($amount / USD_RATE, 0, '.', ',');
}