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
     * Get derived goal values (M, G, D, total_spend_allowed, per_day_allowed_spend, per_day_save_target).
     * Uses existing goal fields: monthly_income = total_money for period, target_amount = goal_save,
     * start_date/end_date for total_days. daily_target is already per_day_allowed_spend.
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
     * Calculate daily save for a check-in. Only used when a check-in exists.
     * New logic:
     * - If daily_spent <= per_day_allowed_spend: daily_save = per_day_save_target + (per_day_allowed_spend - daily_spent)
     *   Example: save_target=300, days=7 → per_day_save_target=42.86; if spend 80 (allowed=100) → save = 42.86 + (100-80) = 62.86
     * - If daily_spent > per_day_allowed_spend: daily_save = 0 (overspending cancels savings).
     *
     * @param float $perDayAllowedSpend From goal (per_day_allowed_spend).
     * @param float $perDaySaveTarget   From goal (goal_save / total_days).
     * @param float $dailySpentAmount   User input for that day.
     * @return float Always >= 0.
     */
    public function calculateDailySave(float $perDayAllowedSpend, float $perDaySaveTarget, float $dailySpentAmount): float
    {
        if ($dailySpentAmount > $perDayAllowedSpend) {
            // Overspending: no savings for this day.
            return 0;
        }
        // Within limit: per_day_save_target + (per_day_allowed_spend - daily_spent)
        $bonus = $perDayAllowedSpend - $dailySpentAmount;
        return round($perDaySaveTarget + $bonus, 2);
    }

    /**
     * Get tracking metrics from existing check-ins only. No row = no calculation.
     * days_passed = COUNT(check-in records); total_saved = SUM(daily_save).
     * Backward compatible: uses calculated_daily_save when set, else non-negative amount_saved.
     *
     * @return array{total_saved: float, days_passed: int, goal_completion_percent: float}
     */
    public function getTracking(Goal $goal): array
    {
        $derived = $this->getGoalDerived($goal);
        $goalSave = $derived['goal_save'];
        $checkIns = $goal->checkIns()->get();

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
