<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProgressPageService
{
    protected $user;

    public function __construct(protected GoalService $goalService)
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
            // dd($activeGoal);
            $journey = [
                'total_saved' => $activeGoal ? number_format($activeGoal['others_data']['saved_amount']) : 0, //$activeGoal['others_data']['saved_amount'] : 0,
                'total_spending' => $activeGoal ? number_format($activeGoal['others_data']['spending_amount']) : 0,
                'goal_target' => $activeGoal ? number_format($activeGoal['target_amount']) : 0,
                'progress_percentage' => $activeGoal ? $activeGoal['others_data']['persantage_progress'] : 0,
            ];
            $best_day_saving = number_format($this->goalService->bestDaySaving() ?? 0);
            $daily_average_saving = number_format($this->goalService->dailyAverageSaving() ?? 0);
            $steak_record = $activeGoal ? $activeGoal['current_streak'] : 0;
            $meeting_daily_target = 'On-Track';
            $check_ins_activity = '';

            $data = [
                'journey' => $journey,
                'best_day_saving' => $best_day_saving,
                'daily_average_saving' => $daily_average_saving,
                'steak_record' => $steak_record,
                'meeting_daily_target' => $meeting_daily_target,
                // 'check_ins_activity' => $check_ins_activity,
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
            $ai_insights = 
                " 'Your weekend savings improved by 40% this month',
                'You are saving 15% above your daily target',
                'Best saving time: Thursday afternoons',"
            ;
            return $ai_insights;
        } catch (Exception $e) {
            Log::error("ProgressPageService::aiInsights" . $e->getMessage());
            throw $e;
        }
    }


}