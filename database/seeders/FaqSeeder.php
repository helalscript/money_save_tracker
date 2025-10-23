<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'id' => 1,
                'question' => 'What is the app about?',
                'answer' => "The app is a smart money-saving tracker that helps you build consistent saving habits.\nSet your saving goals, track your daily or weekly deposits, and keep your streak going."
            ],
            [
                'id' => 2,
                'question' => 'How does the streak system work?',
                'answer' => "Every time you save on a scheduled day, your streak grows. If you miss a saving day, your streak resets.\nIt’s designed to motivate you to save regularly and reach your financial goals faster."
            ],
            [
                'id' => 3,
                'question' => 'Can I create multiple saving goals?',
                'answer' => "Yes. You can create as many goals as you want—like saving for travel, emergency funds, or gadgets.\nEach goal tracks its own progress and streak independently."
            ],
            [
                'id' => 4,
                'question' => 'Is my financial data secure?',
                'answer' => "Absolutely. Your data is stored securely using modern encryption standards.\nWe never share or sell your personal or financial information."
            ],
            [
                'id' => 5,
                'question' => 'Can I use on both web and mobile?',
                'answer' => "Yes. The app comes with a web-based dashboard built in React and can also be accessed via mobile browser.\nAPI support allows you to integrate or extend functionality later."
            ],
            [
                'id' => 6,
                'question' => 'Is free to use?',
                'answer' => "The app offers a free plan with essential features.\nPremium plans unlock advanced analytics, goal insights, and custom streak reminders to help you stay consistent."
            ],

        ];

        DB::table('faqs')->insert($faqs);
    }
}
