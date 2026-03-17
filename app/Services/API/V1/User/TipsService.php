<?php

namespace App\Services\API\V1\User;

use App\Models\CheckIn;
use App\Models\Tip;
use App\Models\UserTip;
use App\Services\AI\OpenAIService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TipsService
{
    protected $user;

    public function __construct(protected GoalService $goalService, protected OpenAIService $openAIService)
    {
        $this->user = Auth::user();
    }

    /**
     * Get daily tips for a user (3 tips per day)
     */
    public function getDailyTips()
    {
        try {
            $today = Carbon::today();

            // Check if user already has a tip for today
            $todayTip = UserTip::where('user_id', $this->user->id)
                ->whereDate('created_at', $today)
                ->latest()
                ->with('tip')
                ->first();

            if (! $todayTip) {
                return $this->refreshTips();
            }

            return $todayTip;
        } catch (Exception $e) {
            Log::error('TipsService::getDailyTips'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark tip as done
     */
    public function markAsDone($userTipId)
    {
        try {
            $userTip = Tip::find($userTipId);
            $userTip->update([
                'status' => 'done',
            ]);

            return $userTip;
        } catch (Exception $e) {
            Log::error('TipsService::markAsDone'.$e->getMessage());
            throw $e;
        }
    }

    public function refreshTips()
    {
        try {
            $today = Carbon::today();
            // count the number of tips for the user per day
            $numberOfTips = UserTip::where('user_id', $this->user->id)
                ->whereDate('created_at', $today)
                ->count();

            if ($numberOfTips >= 3) {
                throw new Exception('You have reached the maximum number of tips for today',200);
            }
            // Last tip text for today (if any) so refresh can ask AI for something different.
            $lastTodayTip = UserTip::where('user_id', $this->user->id)
                ->whereDate('created_at', $today)
                ->latest()
                ->with('tip')
                ->first();

            $previousTipText = $lastTodayTip && $lastTodayTip->tip ? $lastTodayTip->tip->text : null;

            // Build AI context from the active goal and today's spending.
            $goal = $this->goalService->getActiveGoal();

            $dailyLimit = (float) ($goal['others_data']['daily_target'] ?? 0);
            $remainingBudget = (float) ($goal['others_data']['remaining_amount'] ?? 0);
            $remainingDays = (int) ($goal['others_data']['needed_days'] ?? 0);

            $todaySpentQuery = CheckIn::whereHas('goal', function ($query) {
                $query->where('user_id', $this->user->id)
                    ->where('status', 'active');
            })
                ->whereDate('check_date', $today);

            $todaySpent = (float) $todaySpentQuery->sum('amount_spending');
            $categories = $todaySpentQuery->pluck('spending_name')->filter()->unique()->values()->all();

            $context = [
                'daily_limit' => $dailyLimit,
                'today_spent' => $todaySpent,
                'remaining_budget' => $remainingBudget,
                'remaining_days' => $remainingDays,
                'categories' => $categories,
                'previous_tip' => $previousTipText,
            ];

            $tipText = $this->openAIService->generateTip($context);

            // Store the generated tip so the existing structure still works.
            $tip = Tip::create([
                'text' => $tipText,
                'type' => 'moneycoach',
                'status' => 'active',
            ]);

            $userTip = UserTip::create([
                'tip_id' => $tip->id,
                'goal_id' => null,
                'user_id' => $this->user->id,
                'is_seen' => true,
                'seen_at' => now(),
                'status' => 'pending',
            ]);
            $userTip->load('tip');

            return $userTip;
        } catch (Exception $e) {
            Log::error('TipsService::refreshTips'.$e->getMessage());
            throw $e;
        }
    }
}
