<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\V1\Admin\TimePackageService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TimePackageController extends Controller
{
    protected $timePackageService;

    public function __construct(TimePackageService $timePackageService)
    {
        $this->timePackageService = $timePackageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $timePackages = $this->timePackageService->index($request);

            return Helper::jsonResponse(true, 'Time packages fetched successfully', 200, $timePackages, true);
        } catch (Exception $e) {
            Log::error('TimePackageController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'day' => 'nullable|integer',
            'is_unlimited' => 'required|boolean',
        ]);
        
        // If is_unlimited is true, set day to null
        if ($validateData['is_unlimited']) {
            $validateData['day'] = null;
        }
        
        try {
            $timePackage = $this->timePackageService->store($validateData);

            return Helper::jsonResponse(true, 'Time package created successfully', 200, $timePackage);
        } catch (Exception $e) {
            Log::error('TimePackageController::store'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $timePackage = $this->timePackageService->show($id);

            return Helper::jsonResponse(true, 'Time package fetched successfully', 200, $timePackage);
        } catch (Exception $e) {
            Log::error('TimePackageController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'day' => 'nullable|integer',
            'is_unlimited' => 'required|boolean',
        ]);
        
        // If is_unlimited is true, set day to null
        if ($validateData['is_unlimited']) {
            $validateData['day'] = null;
        }
        
        try {
            $timePackage = $this->timePackageService->update($id, $validateData);

            return Helper::jsonResponse(true, 'Time package updated successfully', 200, $timePackage);
        } catch (Exception $e) {
            Log::error('TimePackageController::update'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $timePackage = $this->timePackageService->destroy($id);
            return Helper::jsonResponse(true, 'Time package deleted successfully', 200, $timePackage);
        } catch (Exception $e) {
            Log::error('TimePackageController::destroy'.$e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
