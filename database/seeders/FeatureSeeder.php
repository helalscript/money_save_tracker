<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'title' => 'Unlimited AI Tips',
                'description' => 'Get unlimited AI-powered financial tips and recommendations to improve your money management skills.',
                'image' => null,
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Multi Goal Tracking',
                'description' => 'Track multiple financial goals simultaneously with detailed progress monitoring and insights.',
                'image' => null,
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
            [
                'title' => 'Deeper Insight & Forecasts',
                'description' => 'Get advanced financial insights and forecasts to make better investment and saving decisions.',
                'image' => null,
                'access_level' => 'unlimited',
                'custom_tag' => null,
                'custom_value' => null,
                'status' => 'active',
            ],
        ];

        foreach ($features as $feature) {
            Feature::create($feature);
        }
    }
}
