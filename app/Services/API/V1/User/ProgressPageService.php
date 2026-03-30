<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\CheckIn;
use App\Services\AI\OpenAIService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProgressPageService
{
    protected $user;

    public function __construct(
        protected GoalService $goalService,
        protected OpenAIService $openAIService,
        protected SavingsCalculationService $savingsCalculation,
    ) {
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
            try {
                $goal = $this->goalService->getActiveGoal();
            } catch (Exception $eLog) {
                Log::debug('ProgressPageService::aiInsights: active goal missing', [
                    'message' => $eLog->getMessage(),
                ]);

                return 'No active goal found yet. Start a goal to receive personalized AI insights.';
            }

            $journey = $goal['others_data'] ?? [];

            // Same numbers as CheckIn / SavingsCalculationService (not stale per-row target_amount).
            $derived = $this->savingsCalculation->getGoalDerived($goal);
            $tracking = $this->savingsCalculation->getTracking($goal);
            $guidance = $this->savingsCalculation->getGuidance(
                $goal,
                $tracking['total_saved'],
                $tracking['days_passed']
            );

            $perDayAllowed = (float) ($derived['per_day_allowed_spend'] ?? 0);
            $perDaySaveTarget = (float) ($derived['per_day_save_target'] ?? 0);
            // Must match SavingsCalculationService::getTracking() branch threshold.
            $usesModernDailyModel = $perDayAllowed > 0;

            $sumRowSpent = static function ($row): float {
                return (float) ($row->daily_spent_amount ?? $row->amount_spending ?? 0);
            };

            $savedForDayGroup = function (Collection $dayGroup) use (
                $usesModernDailyModel,
                $perDayAllowed,
                $perDaySaveTarget,
                $sumRowSpent
            ): float {
                if ($usesModernDailyModel) {
                    $totalDailySpent = (float) $dayGroup->sum($sumRowSpent);

                    return $this->savingsCalculation->calculateDailySave(
                        $perDayAllowed,
                        $perDaySaveTarget,
                        $totalDailySpent
                    );
                }
                // Legacy: same row-wise sum as SavingsCalculationService::getTracking() when per_day_allowed_spend <= 0.
                return round((float) $dayGroup->sum(function ($row) {
                    if ($row->calculated_daily_save !== null && (string) $row->calculated_daily_save !== '') {
                        return max(0.0, (float) $row->calculated_daily_save);
                    }
                    $legacy = $row->amount_saved;
                    if ($legacy !== null && (string) $legacy !== '') {
                        return max(0.0, (float) $legacy);
                    }

                    return 0.0;
                }), 2);
            };

            $todayRows = CheckIn::where('goal_id', $goal->id)
                ->whereDate('check_date', Carbon::today())
                ->get();

            $todaySpent = $todayRows->isEmpty()
                ? null
                : round((float) $todayRows->sum($sumRowSpent), 2);
            $todaySaved = $todayRows->isEmpty()
                ? null
                : round($savedForDayGroup($todayRows), 2);

            $recentCheckIns = CheckIn::where('goal_id', $goal->id)
                ->whereDate('check_date', '>=', now()->subDays(14))
                ->orderBy('check_date', 'asc')
                ->get()
                ->groupBy(fn ($checkIn) => $checkIn->check_date->toDateString())
                ->map(function ($dayGroup) use ($sumRowSpent, $perDayAllowed, $perDaySaveTarget, $usesModernDailyModel, $savedForDayGroup) {
                    $spent = round((float) $dayGroup->sum($sumRowSpent), 2);

                    return [
                        'date' => $dayGroup->first()->check_date->toDateString(),
                        'total_spent' => $spent,
                        'implied_daily_saved_amount' => round($savedForDayGroup($dayGroup), 2),
                        'per_day_allowed_spend' => round($perDayAllowed, 2),
                        'per_day_save_target' => round($perDaySaveTarget, 2),
                        'under_or_over_daily_allowance' => $usesModernDailyModel
                            ? round($perDayAllowed - $spent, 2)
                            : null,
                        'check_ins_that_day' => $dayGroup->count(),
                    ];
                })
                ->values()
                ->all();

            $sumLoggedSpending = (float) $goal->checkIns->sum($sumRowSpent);

            $context = [
                'goal_id' => (int) $goal->id,
                'current_streak_days' => (int) ($goal['current_streak'] ?? 0),
                'pace_indicator' => (string) ($goal->deadline_indicator ?? 'unknown'),
                'save_target_amount' => round((float) $derived['goal_save'], 2),
                'goal_period_calendar_days' => (int) $derived['total_days'],
                'calendar_progress' => [
                    'day_index_in_period' => (int) ($journey['current_day'] ?? 1),
                    'calendar_days_until_end_date' => (int) ($journey['needed_days'] ?? 0),
                    'linear_expected_saved_by_today' => round(
                        (float) ($journey['expected_saving_by_today'] ?? 0),
                        2
                    ),
                ],
                'budget_model_exact' => [
                    'per_day_allowed_spend' => round($perDayAllowed, 2),
                    'per_day_save_target' => round($perDaySaveTarget, 2),
                    'total_spend_budget_for_period' => round((float) $derived['total_spend_allowed'], 2),
                ],
                'totals_from_check_ins_exact' => [
                    'total_saved_unique_days' => round((float) $tracking['total_saved'], 2),
                    'unique_days_with_check_in' => (int) $tracking['days_passed'],
                    'goal_completion_percent' => round((float) $tracking['goal_completion_percent'], 2),
                    'sum_logged_spending_all_check_ins' => round($sumLoggedSpending, 2),
                ],
                'guidance_exact' => [
                    'remaining_save_amount' => $guidance['remaining_save'] !== null
                        ? round((float) $guidance['remaining_save'], 2)
                        : null,
                    'remaining_plan_days_by_check_ins' => (int) $guidance['remaining_days'],
                    'next_day_suggested_save_amount' => $guidance['next_day_required_save'] !== null
                        ? round((float) $guidance['next_day_required_save'], 2)
                        : null,
                    'next_day_suggested_allowed_spend' => $guidance['next_day_allowed_spend'] !== null
                        ? round((float) $guidance['next_day_allowed_spend'], 2)
                        : null,
                ],
                'today' => [
                    'has_check_in' => $todayRows->isNotEmpty(),
                    'total_spent' => $todaySpent,
                    'implied_daily_saved' => $todaySaved,
                ],
                'last_14_days_check_ins_by_date' => $recentCheckIns,
            ];

            $insights = $this->openAIService->generateInsights($context);

            // Return plain text (not array/brackets) and clean minor punctuation artifacts.
            $cleanedInsights = array_map(function ($insight) {
                $text = trim((string) $insight);
                $text = trim($text, "[]\"' \t\n\r\0\x0B");
                $text = preg_replace('/\s+/', ' ', $text) ?? $text;

                return $text;
            }, $insights);
            $cleanedInsights = array_filter($cleanedInsights, fn ($insight) => $insight !== '');

            return implode(' ', $cleanedInsights);
        } catch (Exception $e) {
            Log::error("ProgressPageService::aiInsights" . $e->getMessage());
            throw $e;
        }
    }
}