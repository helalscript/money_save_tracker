<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create packages
        $proPackage = Package::create([
            'title' => 'MoneyCoach Pro',
            'price_weekly' => null,
            'price_monthly' => 11.99,
            'price_yearly' => null,
            'price_offer' => null,
            'free_trail_day' => 7,
            'description' => 'Get the full power of AI-driven financial coaching.',
            'image' => 'uploads/packages/pro-package.png',
            'status' => 'active',
        ]); 

        // $premiumPackage = Package::create([
        //     'title' => 'MoneyCoach Premium',
        //     'price_weekly' => null,
        //     'price_monthly' => 29.99,
        //     'price_yearly' => null,
        //     'price_offer' => null,
        //     'free_trail_day' => 7,
        //     'description' => 'Get the full power of AI-driven financial coaching.',
        //     'image' => 'uploads/packages/premium-package.png',
        //     'status' => 'active',
        // ]);

        // Get all features
        $features = Feature::all();

        // Attach all features to both packages
        foreach ($features as $feature) {
            $proPackage->features()->attach($feature->id, [
                'description' => $feature->description,
                'image' => null,
                'status' => 'active',
            ]);

            // $premiumPackage->features()->attach($feature->id, [
            //     'description' => $feature->description,
            //     'image' => null,
            //     'status' => 'active',
            // ]);
        }
    }
}
