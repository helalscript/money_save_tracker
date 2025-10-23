<?php

namespace Database\Seeders;

use App\Models\StepperPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StepperPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stepperPages = [
            [
                'title' => 'Smarter Money with MoneyCoach AI',
                'description' => 'Our AI helps you save smarter, spend wisely, and grow your wealth effortlessly.',
                'image' => 'uploads/stepper/1.jpg',
                'order_no' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Save More Without Stress',
                'description' => 'MoneyCoach AI tracks, optimizes, and helps you reach your goals faster.',
                'image' => 'uploads/stepper/2.jpg',
                'order_no' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Grow Your Wealth Over Time',
                'description' => 'Personalized insights and strategies to make your money work for you',
                'image' => 'uploads/stepper/3.jpg',
                'order_no' => 3,
                'status' => 'active',
            ],
        ];

        foreach ($stepperPages as $page) {
            StepperPage::create($page);
        }
    }
}
