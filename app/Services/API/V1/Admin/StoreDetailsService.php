<?php

namespace App\Services\API\V1\Admin;

use App\Models\StoreDetails;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StoreDetailsService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Fetch all resources.
     *
     * @return mixed
     */
    public function index($request)
    {
        try {
            $perPage = $request->per_page ?? 25;
            $storeDetails = StoreDetails::with(['user:id,name,email,user_name', 'customers:id,store_detail_id,time_package_id,time_package_name,name,rfid_sn_left,rfid_sn_right,email,phone,start_time,end_time,status'])
                ->select('id', 'user_id', 'store_id', 'name', 'email', 'phone', 'batteries', 'contact_name', 'location', 'status')
                // ->paginate($perPage);
                ->paginate($perPage);

            return $storeDetails;
        } catch (Exception $e) {
            Log::error('StoreDetailsService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Store a new resource.
     *
     * @return mixed
     */
    public function store(array $validatedData)
    {
        try {
            // Extract user creation data
            $userData = [
                'name' => $validatedData['contact_name'],
                'user_name' => $validatedData['user_name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'email_verified_at' => now(),
            ];

            // Create user first
            $user = User::create($userData);

            // Remove user creation fields from store details data
            $storeDetailsData = collect($validatedData)->except(['user_name', 'password', 'password_confirmation'])->toArray();
            $storeDetailsData['user_id'] = $user->id;

            // Create store details
            $storeDetails = StoreDetails::create($storeDetailsData);

            // Load the user relationship
            $storeDetails->load('user:id,name,email');

            return $storeDetails;
        } catch (Exception $e) {
            Log::error('StoreDetailsService::store'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Display a specific resource.
     *
     * @return mixed
     */
    public function show(int $id)
    {
        try {
            $storeDetails = StoreDetails::with(['user:id,name,email,password'])->find($id);
            if (! $storeDetails) {
                throw new Exception('Store details not found');
            }

            // Decrypt password for admin viewing
            if ($storeDetails->user && $storeDetails->user->password) {
                $storeDetails->user->password = 'Decrypted: '.$storeDetails->user->password; // For admin viewing
            }

            return $storeDetails;

        } catch (Exception $e) {
            Log::error('StoreDetailsService::show'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific resource.
     *
     * @return mixed
     */
    public function update(int $id, array $validatedData)
    {
        try {
            $storeDetails = StoreDetails::with('user')->find($id);
            if (! $storeDetails) {
                throw new Exception('Store details not found');
            }

            // Update user if user_name or password is provided
            if (isset($validatedData['user_name']) || isset($validatedData['password'])) {
                $user = $storeDetails->user;
                if ($user) {
                    $userUpdateData = [];

                    if (isset($validatedData['user_name'])) {
                        $userUpdateData['user_name'] = $validatedData['user_name'];
                    }
                    if (isset($validatedData['contact_name'])) {
                        $userUpdateData['name'] = $validatedData['contact_name'];
                    }

                    if (isset($validatedData['password'])) {
                        $userUpdateData['password'] = Hash::make($validatedData['password']);
                    }

                    $user->update($userUpdateData);
                }
            }

            // Remove user fields from store details data
            $storeDetailsData = collect($validatedData)->except(['user_name', 'password', 'password_confirmation'])->toArray();
            $storeDetails->update($storeDetailsData);

            // Load the user relationship with password for admin viewing
            $storeDetails->load('user:id,name,email,password');

            // Decrypt password for admin viewing
            if ($storeDetails->user && $storeDetails->user->password) {
                $storeDetails->user->password = 'Decrypted: '.$storeDetails->user->password; // For admin viewing
            }

            return $storeDetails;
        } catch (Exception $e) {
            Log::error('StoreDetailsService::update'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a specific resource.
     *
     * @return mixed
     */
    public function destroy(int $id)
    {
        try {
            $storeDetails = StoreDetails::with('user')->find($id);
            if (! $storeDetails) {
                throw new Exception('Store details not found');
            }

            // Store user info before deletion for response
            $user = $storeDetails->user;

            // Delete the store details first (this will cascade delete customers)
            $storeDetails->delete();

            // Then delete the associated user
            if ($user) {
                $user->delete();
            }

            return $storeDetails;
        } catch (Exception $e) {
            Log::error('StoreDetailsService::destroy'.$e->getMessage());
            throw $e;
        }
    }
}
