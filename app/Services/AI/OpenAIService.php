<?php

namespace App\Services\AI;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    /**
     * Generate a single short coaching tip for the user.
     *
     * @param  array  $context
     * @return string
     *
     * $context keys we expect (best-effort, all optional):
     * - daily_limit
     * - today_spent
     * - remaining_budget
     * - remaining_days
     * - categories (array of strings)
     */
    public function generateTip(array $context): string
    {
        $prompt = $this->buildTipPrompt($context);

        return $this->callChatModel($prompt, 80);
    }

    /**
     * Generate a few higher-level insights for the progress page.
     *
     * @param  array  $context
     * @return array<string>
     */
    public function generateInsights(array $context): array
    {
        $prompt = $this->buildInsightsPrompt($context);
        $raw = $this->callChatModel($prompt, 220);

        // Try to split into bullet-style lines if multiple insights are returned.
        $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];

        $insights = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^[\-\*\d\.\s]+/', '', $line) ?? '');
            if ($line !== '') {
                $insights[] = $line;
            }
        }

        if (empty($insights)) {
            $insights[] = $raw;
        }

        return $insights;
    }

    /**
     * Generate a short goal pace suggestion (1 sentence).
     *
     * @param  array  $context
     */
    public function generateGoalSuggestion(array $context): string
    {
        $cacheKey = 'openai:goal_suggestion:'.sha1(json_encode($context));

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($context) {
            $prompt = $this->buildGoalSuggestionPrompt($context);

            // Keep it short for speed.
            return $this->callChatModel($prompt, 60);
        });
    }

    protected function buildTipPrompt(array $context): string
    {
        $dailyLimit = $context['daily_limit'] ?? null;
        $todaySpent = $context['today_spent'] ?? null;
        $remainingBudget = $context['remaining_budget'] ?? null;
        $remainingDays = $context['remaining_days'] ?? null;
        $categories = $context['categories'] ?? [];
        $previousTip = $context['previous_tip'] ?? null;

        $parts = [];
        $parts[] = 'You are a friendly, non‑judgmental money coach.';
        $parts[] = 'Give ONE short, practical tip (1–2 sentences) to help the user stay within their daily spending limit.';

        if ($dailyLimit !== null) {
            $parts[] = "Daily spend limit: {$dailyLimit}.";
        }
        if ($todaySpent !== null) {
            $parts[] = "Amount spent today: {$todaySpent}.";
        }
        if ($remainingBudget !== null && $remainingDays !== null) {
            $parts[] = "Remaining budget: {$remainingBudget} over {$remainingDays} days.";
        }
        if (! empty($categories)) {
            $parts[] = 'Recent spending areas: '.implode(', ', $categories).'.';
        }

        if ($previousTip) {
            $parts[] = "Do NOT repeat the following previous tip. Previous tip: \"{$previousTip}\".";
        }

        $parts[] = 'Avoid complex financial jargon. Talk like a supportive coach, not a banker.';

        return implode(' ', $parts);
    }

    protected function buildInsightsPrompt(array $context): string
    {
        $parts = [];
        $parts[] = 'You are a friendly, supportive money coach.';
        $parts[] = 'The user’s data is in the JSON below. When you mention any amount, percentage, or number of days, use ONLY values from that JSON—do not estimate or invent figures.';
        $parts[] = 'Return 2–3 short lines of coaching. Put one insight per line. Use clear, grammatical English; no square brackets around the whole reply; no JSON in your answer.';
        $parts[] = 'If you talk about how many days are left until the goal end date, use calendar_progress.calendar_days_until_end_date with deadline_aligned_math (equal_split_save_per_day_to_finish_on_time, implied_daily_spending_headroom_at_that_save_pace). If can_finish_exactly_by_goal_end is false, say it is not fully achievable and focus on maximizing (and mention max_possible_save_per_day / max_total_save_in_remaining_window when needed). Do NOT mix those with guidance_exact.next_day_suggested_save_amount unless both sections agree—guidance_exact is tied to check-in days.';
        $parts[] = 'Tie advice to: save target, per_day_allowed_spend, totals from check-ins, and recent daily spend vs allowance when the data includes it. If there is no check-in today, say so gently.';
        $parts[] = 'Plain language, no financial jargon, each line at most two sentences.';

        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $parts[] = 'User data (JSON): '.$json;

        return implode(' ', $parts);
    }

    protected function buildGoalSuggestionPrompt(array $context): string
    {
        $parts = [];
        $parts[] = 'You are a friendly, non-judgmental money coach.';
        $parts[] = 'Write ONE short sentence (max 18 words) as a goal progress suggestion.';
        $parts[] = 'No bullet points, no quotes, no emojis, no disclaimers.';
        $parts[] = 'If the user is on track or ahead, encourage them briefly.';
        $parts[] = 'If behind, give one actionable step with a specific daily saving target if available.';

        $json = json_encode($context);
        $parts[] = 'JSON: '.$json;

        return implode(' ', $parts);
    }

    /**
     * Low-level OpenAI chat call.
     */
    protected function callChatModel(string $prompt, int $maxTokens): string
    {
        $apiKey = config('services.openai.api_key');
        $baseUrl = rtrim(config('services.openai.api_url', 'https://api.openai.com/v1'), '/');

        if (! $apiKey) {
            Log::warning('OpenAIService: missing API key.');
            return 'Today, try to be just a little more intentional with your spending than yesterday.';
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(8)
                ->retry(1, 200)
                ->post($baseUrl.'/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a concise, supportive money coach for everyday people in the United States.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => $maxTokens,
                    // Lower temperature tends to be faster + more consistent.
                    'temperature' => 0.4,
                ]);

            if (! $response->successful()) {
                Log::error('OpenAIService HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return 'Keep your spending simple today: choose one thing to skip so you stay closer to your limit.';
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? null;

            if (! $text) {
                Log::error('OpenAIService: empty content from API', ['response' => $data]);
                return 'Try to spend a bit less than your limit today so you have more room later in the week.';
            }

            return trim($text);
        } catch (Exception $e) {
            Log::error('OpenAIService exception: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return 'Focus on one small spending decision you can improve today—small choices add up over time.';
        }
    }
}

