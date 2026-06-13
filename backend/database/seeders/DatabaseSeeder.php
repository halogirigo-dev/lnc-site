<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // Create admin user
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'hello@lomboknatureculture.com')],
            [
                'name'     => 'LNC Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'change-me-now!')),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            TourPackageSeeder::class,
            HotelSeeder::class,
            TestimonialSeeder::class,
            TeamMemberSeeder::class,
            GallerySeeder::class,
            FaqSeeder::class,
            DestinationSeeder::class,
        ]);
    }
}
