<?php

namespace App\Services\API\V1\User;

use App\Models\Goal;

/**
 * Encapsulates all savings calculation logic.
 * - Savings are calculated ONLY when a check-in exists (no row = no calculation).
 * - Days passed = COUNT(check-in records); calendar days are NOT assumed.
 * - Overspending never creates negative savings (daily_save = max(raw, 0)).
 */
class SavingsCalculationService
{
    /**
     * Get derived goal values:
     * - total_money (M)            → monthly_income (total budget for the period)
     * - goal_save (G)              → target_amount (how much the user wants to save)
     * - total_days (D)             → days between start_date and end_date (inclusive)
     * - total_spend_allowed        → M - G  (total that can be spent in the period)
     * - per_day_allowed_spend      → (M - G) / D  (daily spending limit, e.g. 700/7 = 100)
     * - per_day_save_target        → G / D        (how much should be saved per day on average; used for guidance only)
     *
     * These values back your example:
     * - total_money = 1000
     * - goal_save   = 300
     * - total_days  = 7
     * - total_spend_allowed   = 700
     * - per_day_allowed_spend = 100
     * - per_day_save_target   ≈ 42.86
     *
     * @return array{total_money: float, goal_save: float, total_days: int, total_spend_allowed: float, per_day_allowed_spend: float, per_day_save_target: float}
     */
    public function getGoalDerived(Goal $goal): array
    {
        $totalMoney = (float) ($goal->monthly_income ?? 0);
        $goalSave = (float) ($goal->target_amount ?? 0);
        $start = $goal->start_date ? \Carbon\Carbon::parse($goal->start_date)->startOfDay() : null;
        $end = $goal->end_date ? \Carbon\Carbon::parse($goal->end_date)->startOfDay() : null;
        $totalDays = ($start && $end) ? max(1, $start->diffInDays($end) + 1) : 1;
        $totalSpendAllowed = max(0, $totalMoney - $goalSave);
        $perDayAllowedSpend = $totalDays > 0 ? ($totalSpendAllowed / $totalDays) : 0;
        // per_day_save_target = save_target / total_days (e.g., 300/7 = 42.86)
        $perDaySaveTarget = $totalDays > 0 ? ($goalSave / $totalDays) : 0;

        return [
            'total_money' => $totalMoney,
            'goal_save' => $goalSave,
            'total_days' => $totalDays,
            'total_spend_allowed' => $totalSpendAllowed,
            'per_day_allowed_spend' => round($perDayAllowedSpend, 2),
            'per_day_save_target' => round($perDaySaveTarget, 2),
        ];
    }

    /**
     * Calculate "today's saved" for a check-in day using your simple rule:
     *
     * - per_day_allowed_spend = daily spending limit (e.g. 700 ÷ 7 = 100)
     * - daily_spent_amount    = how much spent today (e.g. 50)
     * - today_saved           = max(0, per_day_allowed_spend - daily_spent_amount)
     *
     * So in your example:
     * - today_saved = max(0, 100 - 50) = 50 saved
     *
     * Note: per_day_save_target (G ÷ D) is still used for guidance (how much
     * you *should* save per day on average), but does not get added into
     * the per-day saved amount. Progressive savings are:
     *
     *   total_saved = Σ(today_saved) over all check-in days
     *   remaining   = goal_save - total_saved
     *
     * @param float $perDayAllowedSpend From goal (per_day_allowed_spend).
     * @param float $dailySpentAmount   Total spent for that day.
     * @return float Always >= 0.
     */
    public function calculateDailySave(float $perDayAllowedSpend, float $dailySpentAmount): float
    {
        if ($dailySpentAmount >= $perDayAllowedSpend) {
            // Spent at or over the daily limit → no savings for that day.
            return 0.0;
        }

        return round($perDayAllowedSpend - $dailySpentAmount, 2);
    }

    /**
     * Get tracking metrics from existing check-ins only. No row = no calculation.
     *
     * New behaviour (matches your example):
     * - each date should be counted only once:
     *   today_saved(date) = max(0, per_day_allowed_spend - Σ(daily_spent_amount for that date))
     * - total_saved = Σ today_saved(date) for all dates
     * - days_passed = at least 1 check-in date (unique dates)
     *
     * @return array{total_saved: float, days_passed: int, goal_completion_percent: float}
     */
    public function getTracking(Goal $goal): array
    {
        $derived = $this->getGoalDerived($goal);
        $goalSave = $derived['goal_save'];
        $perDayAllowedSpend = $derived['per_day_allowed_spend'];
        $checkIns = $goal->checkIns()->get();

        // Fallback for very old goals where daily limit is not defined.
        if ($perDayAllowedSpend <= 0) {
            $daysPassed = $checkIns->count();
            $totalSaved = (float) $checkIns->sum(function ($row) {
                if ($row->calculated_daily_save !== null && $row->calculated_daily_save !== '') {
                    return max(0, (float) $row->calculated_daily_save);
                }
                $legacy = $row->amount_saved;
                if ($legacy !== null && $legacy !== '') {
                    return max(0, (float) $legacy);
                }
                return 0;
            });
        } else {
            // Group by date so that each calendar day contributes only once.
            $groupedByDate = $checkIns->groupBy(function ($row) {
                return $row->check_date ? $row->check_date->toDateString() : null;
            });

            $daysPassed = 0;
            $totalSaved = 0.0;

            foreach ($groupedByDate as $date => $rows) {
                if ($date === null) {
                    continue;
                }
                $daysPassed++;

                // Total spent on this calendar day (sum of all check-ins' spending).
                $totalDailySpent = (float) $rows->sum(function ($row) {
                    return (float) ($row->daily_spent_amount ?? $row->amount_spending ?? 0);
                });

                // today_saved = max(0, daily_limit - total_daily_spent)
                $todaySaved = $this->calculateDailySave($perDayAllowedSpend, $totalDailySpent);
                $totalSaved += $todaySaved;
            }
        }
        $goalCompletionPercent = $goalSave > 0
            ? min(100, round(($totalSaved / $goalSave) * 100, 2))
            : 0;

        return [
            'total_saved' => round($totalSaved, 2),
            'days_passed' => $daysPassed,
            'goal_completion_percent' => $goalCompletionPercent,
        ];
    }

    /**
     * Dynamic guidance: remaining_days, remaining_save, next_day_required_save, next_day_allowed_spend.
     * remaining_days = D - days_passed (can be 0); avoid division by zero for next_day_*.
     *
     * @param Goal $goal
     * @param float $totalSaved  From getTracking().
     * @param int   $daysPassed  From getTracking().
     * @return array{remaining_days: int, remaining_save: float, next_day_required_save: float|null, next_day_allowed_spend: float|null}
     */
    public function getGuidance(Goal $goal, float $totalSaved, int $daysPassed): array
    {
        $derived = $this->getGoalDerived($goal);
        $goalSave = $derived['goal_save'];
        $totalDays = $derived['total_days'];
        $perDayAllowedSpend = $derived['per_day_allowed_spend'];

        $remainingDays = max(0, $totalDays - $daysPassed);
        $remainingSave = max(0, $goalSave - $totalSaved);

        $nextDayRequiredSave = null;
        $nextDayAllowedSpend = null;
        if ($remainingDays > 0) {
            $nextDayRequiredSave = round($remainingSave / $remainingDays, 2);
            $nextDayAllowedSpend = round(max(0, $perDayAllowedSpend - $nextDayRequiredSave), 2);
        }

        return [
            'remaining_days' => $remainingDays,
            'remaining_save' => round($remainingSave, 2),
            'next_day_required_save' => $nextDayRequiredSave,
            'next_day_allowed_spend' => $nextDayAllowedSpend,
        ];
    }

    /**
     * Build full progress + guidance payload for API. Read-only; no side effects.
     *
     * @return array{today: array, progress: array, guidance: array}
     */
    public function buildProgressPayload(Goal $goal, ?float $todaySpent = null, ?float $todaySaved = null): array
    {
        $derived = $this->getGoalDerived($goal);
        $tracking = $this->getTracking($goal);
        $guidance = $this->getGuidance($goal, $tracking['total_saved'], $tracking['days_passed']);

        $today = [
            'spent' => $todaySpent !== null ? round($todaySpent, 2) : null,
            'saved' => $todaySaved !== null ? round($todaySaved, 2) : null,
        ];

        $progress = [
            'total_saved' => $tracking['total_saved'],
            'goal_save' => $derived['goal_save'],
            'goal_completion_percent' => $tracking['goal_completion_percent'],
            'days_passed' => $tracking['days_passed'],
            'total_days' => $derived['total_days'],
        ];

        return [
            'today' => $today,
            'progress' => $progress,
            'guidance' => $guidance,
        ];
    }
}
