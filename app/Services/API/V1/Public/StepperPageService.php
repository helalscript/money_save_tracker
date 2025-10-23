<?php

namespace App\Services\API\V1\Public;

use App\Models\StepperPage;
use Exception;
use Illuminate\Support\Facades\Log;

class StepperPageService
{
    /**
     * Fetch all active stepper pages with pagination and optional search.
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;
            $search = $request->search;

            $query = StepperPage::where('status', 'active')->select('id', 'title', 'description', 'order_no', 'image');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            return $query->orderBy('order_no')->paginate($perPage);
        } catch (Exception $e) {
            Log::error("StepperPageService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific active stepper page.
     */
    public function show(int $id)
    {
        try {
            $page = StepperPage::where('id', $id)
                ->select('id', 'title', 'description', 'order_no', 'image')
                ->where('status', 'active')
                ->first();

            if (!$page) {
                throw new Exception('Stepper page not found or inactive');
            }

            return $page;
        } catch (Exception $e) {
            Log::error("StepperPageService::show" . $e->getMessage());
            throw $e;
        }
    }
}