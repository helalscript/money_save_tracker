<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tip;
use Carbon\Carbon;

class TipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run()
    {
        $nowTime = Carbon::now();
        $tips = [
            // Money Coach Tips
            [
                'text' => 'Make lunch at home tomorrow and save $8 toward your goal.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Skip that coffee shop visit today and save $5 for your emergency fund.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Review your subscriptions and cancel one you rarely use.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Set aside 10% of today\'s income before spending anything else.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Use cashback apps when shopping online to earn extra savings.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Buy generic brands instead of name brands and save up to 30%.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Plan your meals for the week to avoid impulse grocery purchases.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Wait 24 hours before making any non-essential purchase over $50.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Use the 50/30/20 rule: 50% needs, 30% wants, 20% savings.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Automate your savings - set up automatic transfers on payday.',
                'type' => 'moneycoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],

            // Debt Coach Tips
            [
                'text' => 'Pay more than the minimum on your highest-interest debt this month.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Consider the debt snowball method - pay off smallest debt first for motivation.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Call your credit card company and negotiate a lower interest rate.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Stop using credit cards until your debt is under control.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Use windfalls like tax refunds to make extra debt payments.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Create a debt payoff plan and visualize your progress monthly.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Look for a balance transfer card with 0% APR to reduce interest.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Make bi-weekly payments instead of monthly to pay off debt faster.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Track every dollar you spend to find extra money for debt payments.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Consider a side hustle to generate extra income for debt payoff.',
                'type' => 'debtcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],

            // Goal Coach Tips
            [
                'text' => 'Break your big goal into smaller milestones to stay motivated.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Visualize your goal daily - create a vision board or set phone wallpaper.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Review your progress weekly and adjust your strategy if needed.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Share your goal with a friend for accountability and support.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Celebrate small wins along the way to your big goal.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Set a specific deadline for your goal to create urgency.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Write down why this goal matters to you and read it when motivation drops.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Track your progress visually with charts or graphs.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Create a morning routine that includes reviewing your goals.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
            [
                'text' => 'Focus on one major goal at a time for better success rate.',
                'type' => 'goalcoach',
                'status' => 'active',
                'created_at' => $nowTime,
                'updated_at' => $nowTime,
            ],
        ];

        Tip::insert($tips);
    }
}
