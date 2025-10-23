<?php

namespace App\Services\API\V1\User;

use App\Models\Payment;
use App\Models\Subcription;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all payments for the authenticated user.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;

            $payments = Payment::where('user_id', $this->user->id)
                ->with(['payable'])
                ->orderBy('pay_at', 'desc')
                ->paginate($perPage);

            return $payments;
        } catch (Exception $e) {
            Log::error("PaymentService::index" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new payment.
     *
     * @param array $validatedData
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            $validatedData['user_id'] = $this->user->id;
            
            // Verify the payable (subscription) belongs to the authenticated user
            if ($validatedData['payable_type'] === 'App\\Models\\Subcription') {
                $subscription = Subcription::where('id', $validatedData['payable_id'])
                    ->where('user_id', $this->user->id)
                    ->first();

                if (!$subscription) {
                    throw new Exception('Subscription not found or does not belong to you');
                }
            }
            
            $payment = Payment::create($validatedData);
            
            $payment->load('payable');

            return $payment;
        } catch (Exception $e) {
            Log::error("PaymentService::store" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific payment.
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $payment = Payment::where('id', $id)
                ->where('user_id', $this->user->id)
                ->with('payable')
                ->first();

            if (!$payment) {
                throw new Exception('Payment not found or does not belong to you');
            }

            return $payment;
        } catch (Exception $e) {
            Log::error("PaymentService::show" . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific payment.
     *
     * @param int $id
     * @param array $validatedData
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            $payment = Payment::where('id', $id)
                ->where('user_id', $this->user->id)
                ->first();

            if (!$payment) {
                throw new Exception('Payment not found or does not belong to you');
            }

            $payment->update($validatedData);
            
            $payment->load('payable');

            return $payment;
        } catch (Exception $e) {
            Log::error("PaymentService::update" . $e->getMessage());
            throw $e;
        }
    }
}