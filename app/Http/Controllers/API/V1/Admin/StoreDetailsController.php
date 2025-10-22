<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\Admin\StoreDetailsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreDetailsController extends Controller
{
    protected $storeDetailsService;

    public function __construct(StoreDetailsService $storeDetailsService)
    {
        $this->storeDetailsService = $storeDetailsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $storeDetails = $this->storeDetailsService->index($request);

            return Helper::jsonResponse(true, 'Store details fetched successfully', 200, $storeDetails, true);
        } catch (Exception $e) {
            Log::error('StoreDetailsController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'store_id' => 'required|string|max:100|unique:store_details,store_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'batteries' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            // 'status' => 'nullable|string|in:active,inactive',
            'user_name' => 'required|string|max:255|unique:users,name',
            'password' => 'required|string|min:8',
        ]);
        
        try {
            $storeDetails = $this->storeDetailsService->store($validateData);

            return Helper::jsonResponse(true, 'Store details created successfully', 200, $storeDetails);
        } catch (Exception $e) {
            Log::error('StoreDetailsController::store'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $storeDetails = $this->storeDetailsService->show($id);

            return Helper::jsonResponse(true, 'Store details fetched successfully', 200, $storeDetails);
        } catch (Exception $e) {
            Log::error('StoreDetailsController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'store_id' => 'required|string|max:100|unique:store_details,store_id,'.$id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'phone' => 'required|string|max:20|unique:users,phone,'.$id,
            'batteries' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'status' => 'nullable|string|in:active,inactive',
            'user_name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
        ]);
        
        try {
            $storeDetails = $this->storeDetailsService->update($id, $validateData);

            return Helper::jsonResponse(true, 'Store details updated successfully', 200, $storeDetails);
        } catch (Exception $e) {
            Log::error('StoreDetailsController::update'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $storeDetails = $this->storeDetailsService->destroy($id);
            return Helper::jsonResponse(true, 'Store details deleted successfully', 200);
        } catch (Exception $e) {
            Log::error('StoreDetailsController::destroy'.$e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
