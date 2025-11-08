<?php

namespace App\Services\API\V1\User;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use App\Models\Tip;
use App\Models\UserTip;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TipsService
{
    protected $user;

    public function __construct()
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

            // Check if user already has tips for today
            $todayTips = UserTip::where('user_id', $this->user->id)
                ->whereDate('created_at', $today)
                ->latest()
                ->with('tip')
                ->first();
            // Return today's tips if they exist
            if (!$todayTips) {
                return $this->refreshTips();
            }
            return $todayTips;
        } catch (Exception $e) {
            Log::error("TipsService::getDailyTips" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark tip as done
     */
    public function markAsDone($userTipId)
    {
        try {
            $userTip = UserTip::where('id', $userTipId)
                ->where('user_id', $this->user->id)
                ->firstOrFail();

            $userTip->update([
                'status' => 'done',
            ]);

            return $userTip;
        } catch (Exception $e) {
            Log::error("TipsService::markAsDone" . $e->getMessage());
            throw $e;
        }
    }

    public function refreshTips()
    {
        try {
            $today = Carbon::today();

            // Check if user already has tips for today
            $todayTips = UserTip::where('user_id', $this->user->id)
                ->whereDate('created_at', $today)
                ->with('tip')
                ->get();
                
            //in future change to get multiple tips with subcription plan
            // If user already has 3 tips for today, return them
            if ($todayTips->count() >= 3) {
                throw new Exception('User already has 3 tips for today',200);
            }
            // Get tips user hasn't received yet
            $excludeTipIds = UserTip::where('user_id', $this->user->id)
                ->pluck('tip_id')
                ->toArray();
            if ($excludeTipIds) {
                // Build query for new tips
                $query = Tip::where('status', 'active')
                    ->whereNotIn('id', $excludeTipIds);
            } else {
                $query = Tip::where('status', 'active');
            }

            // Get 1 random tips
            $newTips = $query->inRandomOrder()->first();
            if (!$newTips) {
                throw new Exception('No new tips available',200);
            }
            // Create user_tips records
            $userTip = UserTip::create([
                'tip_id' => $newTips->id,
                'goal_id' => null,
                'user_id' => $this->user->id,
                'is_seen' => true,
                'seen_at' => now(),
                'status' => 'pending',
            ]);
            $userTip->load('tip');
            return $userTip;
        } catch (Exception $e) {
            Log::error("TipsService::refreshTips" . $e->getMessage());
            throw $e;
        }
    }

}