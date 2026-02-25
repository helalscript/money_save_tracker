<?php

namespace App\Services\API\V1\User;

use App\Models\Goal;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoalService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all goals for the authenticated user.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;

            $goals = Goal::with([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                },
            ])
                ->where([
                    'status' => 'active',
                    'user_id' => $this->user->id,
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $goals;
        } catch (Exception $e) {
            Log::error('GoalService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new goal.
     *
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            DB::beginTransaction();
            $validatedData['start_date'] = Carbon::today()->toDateString();
            $startDate = Carbon::parse($validatedData['start_date']);
            $endDate = Carbon::parse($validatedData['end_date']);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            // total_spend_allowed = monthly_income - target_amount (total money available for spending).
            $monthlyIncome = (float) ($validatedData['monthly_income'] ?? 0);
            $amountToSave = (float) ($validatedData['target_amount'] ?? 0);
            $validatedData['total_spend_allowed'] = round(max(0, $monthlyIncome - $amountToSave), 2);
            // daily_budget = total_spend_allowed / total_days; stored as daily_target (max spend per day).
            $validatedData['daily_target'] = $totalDays > 0 ? round($validatedData['total_spend_allowed'] / $totalDays, 2) : 0;

            $validatedData['user_id'] = $this->user->id;
            $validatedData['status'] = 'active';

            $goal = Goal::create($validatedData);

            $goal->load([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                },
            ]);
            // satus update other goals
            Goal::where('user_id', $this->user->id)
                ->where('id', '!=', $goal->id)
                ->update(['status' => 'inactive']);

            DB::commit();

            return $goal;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('GoalService::store'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific goal.
     *
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $goal = Goal::where([
                'id' => $id,
                'user_id' => $this->user->id,
                'status' => 'active',
            ])
                ->with([
                    'checkIns' => function ($query) {
                        $query->orderBy('check_date', 'desc');
                    },
                ])
                ->first();

            if (! $goal) {
                throw new Exception('Goal not found or does not belong to you');
            }
            $goal['current_streak'] = $this->calculateStreak($goal);
            $goal['others_data'] = $this->calculateNewEndDate($goal);

            return $goal;
        } catch (Exception $e) {
            Log::error('GoalService::show'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific goal.
     *
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            $goal = Goal::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (! $goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

            $goal->update($validatedData);

            $goal->load([
                'checkIns' => function ($query) {
                    $query->orderBy('check_date', 'desc');
                },
            ]);

            return $goal;
        } catch (Exception $e) {
            Log::error('GoalService::update'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific goal.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            $goal = Goal::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (! $goal) {
                throw new Exception('Goal not found or does not belong to you');
            }

            $goal->delete();

            return $goal;
        } catch (Exception $e) {
            Log::error('GoalService::destroy'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Compute savings-based progress for the active goal.
     * Simplified calculation: total_saved = total_spend_allowed - total_spent.
     * Where total_spent = sum of all check-ins' daily_spent_amount (or amount_spending for legacy).
     */
    public function calculateNewEndDate($goal)
    {
        $startDate = Carbon::parse($goal->start_date)->startOfDay();
        $endDate = Carbon::parse($goal->end_date)->startOfDay();
        $totalDays = max(1, $startDate->diffInDays($endDate) + 1);
        $targetSaving = (float) ($goal->target_amount ?? 0);
        $monthlyIncome = (float) ($goal->monthly_income ?? 0);
        // total_spend_allowed = monthly_income - target_amount; calculate if null (for existing goals).
        $totalSpendAllowed = (float) ($goal->total_spend_allowed ?? 0);
        if ($totalSpendAllowed <= 0) {
            $totalSpendAllowed = max(0, $monthlyIncome - $targetSaving);
        }
        $dailyBudget = (float) ($goal->daily_target ?? 0);

        // Total spent = sum of all check-ins' daily_spent_amount (or amount_spending for legacy).
        $totalSpent = (float) $goal->checkIns()->get()->sum(function ($c) {
            return (float) ($c->daily_spent_amount ?? $c->amount_spending ?? 0);
        });

        // Total saved = total_spend_allowed - total_spent (simpler calculation).
        $totalSaved = max(0, $totalSpendAllowed - $totalSpent);

        // Remaining saving = target_saving - total_saved (can be negative if ahead).
        $remainingSaving = $targetSaving - $totalSaved;
        $spendingAmount = $totalSpent;

        // Progress percentage = (total_saved / target_saving) * 100; clamp 0–100 for display.
        $progressPercent = $targetSaving > 0
            ? min(100.0, max(0.0, ($totalSaved / $targetSaving) * 100))
            : 0.0;

        $today = Carbon::today()->startOfDay();
        $effectiveToday = $today->lte($endDate) ? $today : $endDate;
        $daysElapsed = max(0, $startDate->diffInDays($effectiveToday) + 1);
        $currentDay = (int) min($totalDays, max(1, $daysElapsed));
        $daysRemaining = max(0, $totalDays - $daysElapsed);

        // Expected saving by today = (target_saving / total_days) × current_day.
        $expectedSavingByToday = $totalDays > 0 ? ($targetSaving / $totalDays) * $currentDay : 0;

        // Status message: if total_saved >= expected_saving -> on track / early; else need to save more.
        if ($totalSaved >= $expectedSavingByToday) {
            $statusMessage = "At your pace, you'll reach your goal early";
        } else {
            $statusMessage = 'You need to save more per day to reach your goal';
        }

        // Deadline indicator: green = on track, yellow = slightly behind, red = falling behind.
        $ratio = $expectedSavingByToday > 0 ? $totalSaved / $expectedSavingByToday : 1.0;
        $this->updateGoalDeadlineIndicatorFromSavings($goal, $ratio);

        $dailyTargetFormatted = number_format($dailyBudget, 2, '.', '');
        $progressFormatted = number_format($progressPercent, 2, '.', '');

        return [
            'needed_days' => $daysRemaining,
            'original_end_date' => $endDate->toDateString(),
            'new_end_date' => $endDate->toDateString(),
            'saved_amount' => $totalSaved,
            'save_target' => $targetSaving,
            'remaining_saving' => $remainingSaving,
            'spending_amount' => $spendingAmount,
            'remaining_amount' => $remainingSaving,
            'daily_target' => $dailyTargetFormatted,
            'deadline_indicator' => $goal->deadline_indicator ?? 'green',
            'persantage_progress' => $progressFormatted,
            'status_message' => $statusMessage,
            'expected_saving_by_today' => $expectedSavingByToday,
            'current_day' => $currentDay,
            'total_days' => $totalDays,
            'total_spend_allowed' => round($totalSpendAllowed, 2),
        ];
    }

    /**
     * Set deadline_indicator from savings ratio (total_saved / expected_saving_by_today).
     */
    protected function updateGoalDeadlineIndicatorFromSavings($goal, $savingsRatio)
    {
        if ($savingsRatio >= 1.0) {
            $goal->update(['deadline_indicator' => 'green']);
        } elseif ($savingsRatio >= 0.8) {
            $goal->update(['deadline_indicator' => 'yellow']);
        } else {
            $goal->update(['deadline_indicator' => 'red']);
        }
    }

    public function calculateStreak($goal)
    {
        // Get all checking dates from `checkIns` table
        $checkingDates = $goal->checkIns()
            ->orderBy('check_date', 'desc')
            ->pluck('check_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->values()
            ->toArray();

        // If no check-ins exist, streak is 0
        if (empty($checkingDates)) {
            $goal->update(['current_streak' => 0]);

            return 0;
        }

        $streak = 0;
        $today = Carbon::today()->toDateString();
        $checkDate = Carbon::today();

        // Check if today has a check-in
        $todayHasCheckIn = in_array($today, $checkingDates);

        // If today has no check-in, check from yesterday
        if (! $todayHasCheckIn) {
            $checkDate->subDay();
        }

        // Count consecutive days from checkDate backward
        while (in_array($checkDate->toDateString(), $checkingDates)) {
            $streak++;
            $checkDate->subDay();
        }

        // Goal update with new streak count
        $goal->update(['current_streak' => $streak]);

        return $streak;
    }

    public function updateGoalDeadlineIndicator($goal, $spendPercentage, $timePercentage)
    {
        // Compare how fast the user is spending vs how fast time is passing.
        // If spending progress is comfortably below time progress -> green.
        // Slightly above -> yellow. Significantly above -> red.

        // If we have no timeline information, default to green.
        if ($timePercentage <= 0) {
            $goal->update(['deadline_indicator' => 'green']);

            return;
        }

        // Ratio of budget used vs time elapsed.
        $ratio = $spendPercentage / $timePercentage;

        if ($ratio <= 1.0) {
            // On or under track
            $goal->update(['deadline_indicator' => 'green']);
        } elseif ($ratio <= 1.2) {
            // Up to ~20% ahead of planned pace
            $goal->update(['deadline_indicator' => 'yellow']);
        } else {
            // Spending much faster than time progress
            $goal->update(['deadline_indicator' => 'red']);
        }
    }

    public function getActiveGoal()
    {
        try {
            // $goal = Goal::where([
            //     'user_id' => $this->user->id,
            //     'status' => 'active',
            // ])->with([
            //             'checkIns' => function ($query) {
            //                 $query->orderBy('check_date', 'desc');
            //             }
            //         ])
            //     ->first();
            // if (!$goal) {
            //     throw new Exception('Goal not found or does not belong to you');
            // }
            $goal = $this->nowActiveGoal();
            $goal['current_streak'] = $this->calculateStreak($goal);
            $goal['others_data'] = $this->calculateNewEndDate($goal);
            return $goal;
        } catch (Exception $e) {
            Log::error('GoalService::getActiveGoal'.$e->getMessage());
            throw $e;
        }
    }

    public function getWeeklyStreakData()
    {
        $goal = Goal::where([
            'user_id' => $this->user->id,
            'status' => 'active',
        ])->with([
            'checkIns' => function ($query) {
                $query->orderBy('check_date', 'desc');
            },
        ])
            ->first();
        // Get current streak
        $currentStreak = $this->calculateStreak($goal);

        // Get all check-in dates
        $checkInDates = $goal->checkIns()
            ->pluck('check_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->unique()
            ->toArray();

        // Get current week (Mon to Sun)
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek(); // Sunday

        // Prepare weekly data
        $weeklyData = [];
        $currentDate = $startOfWeek->copy();

        for ($i = 0; $i < 7; $i++) {
            $dateString = $currentDate->toDateString();
            $dayName = $currentDate->format('D'); // Mon, Tue, Wed...

            $weeklyData[] = [
                'date' => $dateString,
                'day' => $dayName,
                'day_full' => $currentDate->format('l'), // Monday, Tuesday...
                'is_checked' => in_array($dateString, $checkInDates),
                'is_today' => $currentDate->isToday(),
                'is_future' => $currentDate->isFuture(),
            ];

            $currentDate->addDay();
        }

        return [
            'current_streak' => $currentStreak,
            'streak_text' => $currentStreak.' day streak!',
            'message' => $currentStreak > 0 ? 'Keep it going!' : 'Start your streak today!',
            'week_range' => $startOfWeek->format('M d').' - '.$endOfWeek->format('M d'),
            'weekly_checkins' => $weeklyData,
        ];
    }

    /**
     * Best single-day saved amount = max over check-ins of (daily_saved_amount).
     * Uses amount_saved when set; else daily_budget - amount_spending (clamped >= 0 for "best day").
     */
    public function bestDaySaving()
    {
        try {
            $goal = $this->nowActiveGoal();
            $checkIns = $goal->checkIns;

            if ($checkIns->isEmpty()) {
                return 0;
            }

            $bestMargin = 0;
            foreach ($checkIns as $checkIn) {
                $saved = $checkIn->amount_saved;
                if ($saved !== null && (string) $saved !== '') {
                    $margin = max(0, (float) $saved);
                } else {
                    $dailyLimit = (float) ($checkIn->target_amount ?? 0);
                    $spent = (float) ($checkIn->amount_spending ?? 0);
                    $margin = max(0, $dailyLimit - $spent);
                }
                if ($margin > $bestMargin) {
                    $bestMargin = $margin;
                }
            }
            return $bestMargin;
        } catch (Exception $e) {
            Log::error('GoalService::bestDaySaving'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Daily average saved = total_saved / number of check-in days.
     * Uses amount_saved when set; else daily_budget - amount_spending per check-in.
     */
    public function dailyAverageSaving()
    {
        try {
            $goal = $this->nowActiveGoal();
            $checkIns = $goal->checkIns;

            if ($checkIns->isEmpty()) {
                return 0;
            }

            $totalMargin = 0;
            $daysCount = 0;
            foreach ($checkIns as $checkIn) {
                $saved = $checkIn->amount_saved;
                if ($saved !== null && (string) $saved !== '') {
                    $margin = (float) $saved;
                } else {
                    $dailyLimit = (float) ($checkIn->target_amount ?? 0);
                    $spent = (float) ($checkIn->amount_spending ?? 0);
                    $margin = $dailyLimit - $spent;
                }
                $totalMargin += $margin;
                $daysCount++;
            }
            if ($daysCount === 0) {
                return 0;
            }
            return $totalMargin / $daysCount;
        } catch (Exception $e) {
            Log::error('GoalService::dailyAverageSaving'.$e->getMessage());
            throw $e;
        }
    }

    public function nowActiveGoal()
    {
        $goal = Goal::where([ 'user_id' => $this->user->id,'status' => 'active'])
        ->with([
            'checkIns' => function ($query) {
                $query->orderBy('check_date', 'desc');
            },
        ])
            ->first();
        if (! $goal) {
            throw new Exception('Goal not found or does not belong to you');
        }

        return $goal;
    }
}
