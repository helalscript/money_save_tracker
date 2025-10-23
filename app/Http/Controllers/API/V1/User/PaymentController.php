<?php

namespace App\Http\Controllers\API\V1\User;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Services\API\V1\User\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $payments = $this->paymentService->index($request);

            return Helper::jsonResponse(true, 'Payments fetched successfully', 200, $payments, true);
        } catch (Exception $e) {
            Log::error('PaymentController::index'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'payable_id' => 'required|integer',
            'payable_type' => 'required|string|in:App\\Models\\Subcription',
            'pay_at' => 'required|date',
            'intend_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,completed,failed,cancelled',
        ]);
        
        try {
            $payment = $this->paymentService->store($validateData);

            return Helper::jsonResponse(true, 'Payment created successfully', 200, $payment);
        } catch (Exception $e) {
            Log::error('PaymentController::store'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $payment = $this->paymentService->show($id);

            return Helper::jsonResponse(true, 'Payment fetched successfully', 200, $payment);
        } catch (Exception $e) {
            Log::error('PaymentController::show'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateData = $request->validate([
            'pay_at' => 'required|date',
            'intend_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'status' => 'nullable|string|in:pending,completed,failed,cancelled',
        ]);
        
        try {
            $payment = $this->paymentService->update($id, $validateData);

            return Helper::jsonResponse(true, 'Payment updated successfully', 200, $payment);
        } catch (Exception $e) {
            Log::error('PaymentController::update'.$e->getMessage());

            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
