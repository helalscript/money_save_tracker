<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\CustomerService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $customers = $this->customerService->index($request);

            return Helper::jsonResponse(true, 'Customers fetched successfully', 200, $customers, true);
        } catch (Exception $e) {
            Log::error('CustomerController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'time_package_id' => 'required|integer|exists:time_packages,id',
            'name' => 'required|string|max:255',
            'rfid_sn_left' => 'required|string|max:255',
            'rfid_sn_right' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            // 'start_time' => 'required|date',
            // 'end_time' => 'required|date|after:start_time',
            // 'status' => 'nullable|string|in:active,inactive',
        ]);
        
        try {
            $customer = $this->customerService->store($validateData);

            return Helper::jsonResponse(true, 'Customer created successfully', 200, $customer);
        } catch (Exception $e) {
            Log::error('CustomerController::store'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $customer = $this->customerService->show($id);

            return Helper::jsonResponse(true, 'Customer fetched successfully', 200, $customer);
        } catch (Exception $e) {
            Log::error('CustomerController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'time_package_id' => 'required|integer|exists:time_packages,id',
            'name' => 'required|string|max:255',
            'rfid_sn_left' => 'required|string|max:255',
            'rfid_sn_right' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);
        
        try {
            $customer = $this->customerService->update($id, $validateData);

            return Helper::jsonResponse(true, 'Customer updated successfully', 200, $customer);
        } catch (Exception $e) {
            Log::error('CustomerController::update'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $customer = $this->customerService->destroy($id);
            return Helper::jsonResponse(true, 'Customer deleted successfully', 200, $customer);
        } catch (Exception $e) {
            Log::error('CustomerController::destroy'.$e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
