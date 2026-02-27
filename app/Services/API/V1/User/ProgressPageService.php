<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\CheckIn;
use App\Services\AI\OpenAIService;

class ProgressPageService
{
    protected $user;

    public function __construct(protected GoalService $goalService, protected OpenAIService $openAIService)
    {
        $this->user = Auth::user();
    }
    /**
     * Fetch all resources.
     *
     * @return mixed
     */
    public function index()
    {
        try {
            $activeGoal = $this->goalService->getActiveGoal();
            
            // total_saved = sum of daily_saved from check-ins; remaining = target - total_saved.
            $totalSaved = $activeGoal ? (float) ($activeGoal['others_data']['saved_amount'] ?? 0) : 0;
            $journey = [
                'total_saved' => $activeGoal ? number_format($totalSaved) : 0,
                'total_spending' => $activeGoal ? number_format($activeGoal['others_data']['spending_amount']) : 0,
                'goal_target' => $activeGoal ? number_format($activeGoal['target_amount']) : 0,
                'progress_percentage' => $activeGoal ? $activeGoal['others_data']['persantage_progress'] : 0,
            ];
            $best_day_saving = number_format($this->goalService->bestDaySaving() ?? 0);
            $daily_average_saving = number_format($this->goalService->dailyAverageSaving() ?? 0);
            $steak_record = $activeGoal ? $activeGoal['current_streak'] : 0;
            $meeting_daily_target = $activeGoal && isset($activeGoal['others_data']['status_message'])
                ? (str_contains($activeGoal['others_data']['status_message'], 'early') ? 'On-Track' : 'Need to save more')
                : 'On-Track';

            $data = [
                'journey' => $journey,
                'best_day_saving' => $best_day_saving,
                'daily_average_saving' => $daily_average_saving,
                'steak_record' => $steak_record,
                'meeting_daily_target' => $meeting_daily_target,
            ];
            return $data;

        } catch (Exception $e) {
            Log::error("ProgressPageService::index" . $e->getMessage());
            throw $e;

        }
    }

    public function aiInsights()
    {
        try {
            $goal = $this->goalService->getActiveGoal();

            $journey = $goal['others_data'] ?? [];

            // Collect recent spending (last 14 days) for richer context.
            $recentCheckIns = CheckIn::whereHas('goal', function ($query) {
                $query->where('user_id', $this->user->id)
                    ->where('status', 'active');
            })
                ->whereDate('check_date', '>=', now()->subDays(14))
                ->orderBy('check_date', 'asc')
                ->get()
                ->groupBy(fn ($checkIn) => $checkIn->check_date->toDateString())
                ->map(function ($dayGroup) {
                    return [
                        'date' => $dayGroup->first()->check_date->toDateString(),
                        'total_spent' => (float) $dayGroup->sum('amount_spending'),
                        'daily_limit' => (float) $dayGroup->first()->target_amount,
                    ];
                })
                ->values()
                ->all();

            $context = [
                'goal_target' => (float) ($goal['target_amount'] ?? 0),
                'total_spending' => (float) ($journey['spending_amount'] ?? 0),
                'remaining_budget' => (float) ($journey['remaining_amount'] ?? 0),
                'daily_limit' => (float) ($journey['daily_target'] ?? 0),
                'days_remaining' => (int) ($journey['needed_days'] ?? 0),
                'current_streak' => (int) ($goal['current_streak'] ?? 0),
                'recent_days' => $recentCheckIns,
            ];

            return $this->openAIService->generateInsights($context);
        } catch (Exception $e) {
            Log::error("ProgressPageService::aiInsights" . $e->getMessage());
            throw $e;
        }
    }


}