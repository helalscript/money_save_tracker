<?php

namespace App\Services\API\V1\User;

use App\Models\Customer;
use App\Models\StoreDetails;
use App\Models\TimePackage;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Calculate start_time and end_time based on time package.
     *
     * @param int $timePackageId
     * @return array
     */
    private function calculateTimeFromPackage(int $timePackageId): array
    {
        $timePackage = TimePackage::find($timePackageId);
        
        if (!$timePackage) {
            throw new Exception('Time package not found');
        }

        $startTime = Carbon::now();
        
        if ($timePackage->is_unlimited) {
            // For unlimited packages, set end_time to 10 years from now
            $endTime = Carbon::now()->addYears(10);
        } else {
            // For limited packages, add the specified days
            $endTime = Carbon::now()->addDays((int) $timePackage->day);
        }

        return [
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }

    /**
     * Fetch all customers for the authenticated user's store.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;

            // Get the user's store details
            $storeDetails = StoreDetails::where('user_id', $this->user->id)->first();

            if (! $storeDetails) {
                throw new Exception('Store details not found for this user');
            }

            // Get customers for this store
            $customers = Customer::select('id', 'store_detail_id', 'time_package_id', 'name', 'rfid_sn_left', 'rfid_sn_right', 'phone', 'email', 'start_time', 'end_time', 'status')
                ->where('store_detail_id', $storeDetails->id)
                ->with(['timePackage:id,name,description,day,is_unlimited'])
                ->paginate($perPage);

            return $customers;
        } catch (Exception $e) {
            Log::error('CustomerService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new customer for the authenticated user's store.
     *
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            // Get the user's store details
            $storeDetails = StoreDetails::where('user_id', $this->user->id)->first();
            if (! $storeDetails) {
                throw new Exception('Store details not found for this user');
            }

            // Calculate start_time and end_time from time package
            $timeData = $this->calculateTimeFromPackage($validatedData['time_package_id']);
            
            // Get time package name
            $timePackage = TimePackage::find($validatedData['time_package_id']);
            $timePackageName = $timePackage->name;

            // Add store_detail_id and time data to the validated data
            $validatedData['store_detail_id'] = $storeDetails->id;
            $validatedData['time_package_name'] = $timePackageName;
            $validatedData['start_time'] = $timeData['start_time'];
            $validatedData['end_time'] = $timeData['end_time'];

            // Create customer
            $customer = Customer::create($validatedData);

            // Load the time package relationship
            $customer->load('timePackage:id,name,description,day,is_unlimited');

            return $customer;
        } catch (Exception $e) {
            Log::error('CustomerService::store'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific customer for the authenticated user's store.
     *
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            // Get the user's store details
            $storeDetails = StoreDetails::where('user_id', $this->user->id)->first();

            if (! $storeDetails) {
                throw new Exception('Store details not found for this user');
            }

            // Get customer for this store
            $customer = Customer::with(['timePackage:id,name,description,day,is_unlimited'])
                ->where('id', $id)
                ->where('store_detail_id', $storeDetails->id)
                ->first();

            if (! $customer) {
                throw new Exception('Customer not found or does not belong to your store');
            }

            return $customer;
        } catch (Exception $e) {
            Log::error('CustomerService::show'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific customer for the authenticated user's store.
     *
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            // Get the user's store details
            $storeDetails = StoreDetails::where('user_id', $this->user->id)->first();
            if (! $storeDetails) {
                throw new Exception('Store details not found for this user');
            }

            // Get customer for this store
            $customer = Customer::where('id', $id)
                ->where('store_detail_id', $storeDetails->id)
                ->first();
            
            if (! $customer) {
                throw new Exception('Customer not found or does not belong to your store');
            }

            // Calculate new end_time from time package (only end_time gets updated)
            $timeData = $this->calculateTimeFromPackage($validatedData['time_package_id']);
            
            // Get time package name
            $timePackage = TimePackage::find($validatedData['time_package_id']);
            $timePackageName = $timePackage->name;

            // Set the time package name and new end_time
            $validatedData['time_package_name'] = $timePackageName;
            $validatedData['end_time'] = $timeData['end_time'];
            // Keep the original start_time (don't update it)
            unset($validatedData['start_time']);

            // Update customer
            $customer->update($validatedData);

            // Load the time package relationship
            $customer->load('timePackage:id,name,description,day,is_unlimited');

            return $customer;
        } catch (Exception $e) {
            Log::error('CustomerService::update'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific customer for the authenticated user's store.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            // Get the user's store details
            $storeDetails = StoreDetails::where('user_id', $this->user->id)->first();

            if (! $storeDetails) {
                throw new Exception('Store details not found for this user');
            }

            // Get customer for this store
            $customer = Customer::where('id', $id)
                ->where('store_detail_id', $storeDetails->id)
                ->first();

            if (! $customer) {
                throw new Exception('Customer not found or does not belong to your store');
            }

            // Delete customer
            $customer->delete();

            return $customer;
        } catch (Exception $e) {
            Log::error('CustomerService::destroy'.$e->getMessage());
            throw $e;
        }
    }
}
