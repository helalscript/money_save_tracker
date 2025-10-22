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
                'id' => 6,
                'question' => 'Can I cancel anytime?',
                'answer' => "Yes. No contracts, no hidden fees, no strings attached.\nYou’re in full control; cancel or upgrade anytime from your account settings."
            ],
            [
                'id' => 7,
                'question' => 'What makes Karially different from free tools?',
                'answer' => "Karially isn’t just another resume builder. It’s a career execution platform.\nYou’ll get access to structured tools to help you:\n• Set and track your goals\n• Build tailored resumes using AI\n• Stay on top of your job applications\n• Get clarity on what to do next\nFree tools give you templates. Karially gives you momentum."
            ],
            [
                'id' => 8,
                'question' => 'What if I don’t know where to start?',
                'answer' => "That’s exactly why Karially exists.\nWhether you’re pivoting careers, re-entering the workforce, or trying to get unstuck, Karially’s tools and prompts will guide you — step by step — to build confidence and take action."
            ],
            [
                'id' => 9,
                'question' => 'Is the coaching included in all plans?',
                'answer' => 'Only in the Premium plan. It includes a 60-minute 1:1 coaching session every month to help you apply strategy, troubleshoot roadblocks, and stay accountable.'
            ],
            [
                'id' => 10,
                'question' => 'Will I lose my data if I cancel?',
                'answer' => 'Nope. Your data is safely stored, and you can access it again if you decide to come back. You just won’t be able to use premium features after cancellation.'
            ],
            [
                'id' => 11,
                'question' => 'Do you offer refunds?',
                'answer' => 'Since Karially is a digital product with instant access, we don’t offer refunds. However, if something’s not working as expected, reach out — we’re here to help and resolve any issues quickly.'
            ],
            [
                'id' => 12,
                'question' => 'Is my data safe with Karially?',
                'answer' => "Yes — your privacy and security are a priority.\nWe use secure, encrypted connections (SSL) and industry-standard protocols to protect your data. We don’t sell your information, and only store what’s necessary to help you use the platform.\nAll resume data, career inputs, and account details are stored securely.\nYou’re building your future; your data deserves the same level of care."
            ],
            [
                'id' => 13,
                'question' => 'What payment methods do you accept?',
                'answer' => "We accept all major credit and debit cards, including:\n• Visa\n• Mastercard\n• American Express\n• Discover\nPayments are processed securely via Stripe. You’ll receive a receipt by email for each billing cycle, and you can manage your subscription anytime from your account."
            ],
        ];

        DB::table('faqs')->insert($faqs);
    }
}
