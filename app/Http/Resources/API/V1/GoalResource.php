<?php

namespace App\Http\Resources\API\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'title' => "Save $" . number_format($this->target_amount, 0) . " by " . Carbon::parse($this->end_date)->format('M d'),
            // 'monthly_income' => $this->monthly_income,
            // 'fixed_expense' => $this->fixed_expense,
            // 'alredy_saved' => $this->alredy_saved,
            // 'debt_blance' => $this->debt_blance,
            // 'goal_type' => $this->goal_type,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'start_date' => $this->start_date,
            // 'end_date' => $this->end_date,
            // 'daily_target' => $this->daily_target,
            // 'status' => $this->status,
            'saved_amount' => number_format($this->others_data['saved_amount'], 0),
            'target_amount' => number_format($this->target_amount, 0),
            'remaining_amount' => number_format($this->others_data['remaining_amount'], 0),
 
            'deadline_indicator' => $this->deadline_indicator,
            'new_end_date' => $this->others_data['new_end_date'],
            'ai_suggestion' => "At your pace. You'll reach your goal by " . Carbon::parse($this->others_data['new_end_date'])->format('M d'),
            'persantage_progress' => $this->others_data['persantage_progress'],
        ];
    }
}
