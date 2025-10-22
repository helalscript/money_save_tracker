<?php

namespace App\Services\API\V1\Admin\System;

use App\Helpers\Helper;
use App\Models\SystemSetting;
use Artisan;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemSettingService
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
    public function index()
    {
        try {
            $systemSettings = SystemSetting::latest('id')->first();

            return $systemSettings;

        } catch (Exception $e) {
            Log::error('SystemSettingService::index'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a specific resource.
     *
     * @return mixed
     */
    public function update(array $validatedData)
    {
        try {
            $setting = SystemSetting::firstOrNew();

            foreach (['favicon', 'logo','footer_logo'] as $field) {
                if (isset($validatedData[$field]) && $validatedData[$field]) {
                    // Get relative path if the model accessor returns full URL
                    $oldPath = $setting->$field
                        ? str_replace(url('/').'/', '', $setting->$field)
                        : null;

                    // Delete old file if it exists
                    if ($oldPath && file_exists(public_path($oldPath))) {
                        Helper::fileDelete(public_path($oldPath));
                    }

                    // Upload new file
                    $validatedData[$field] = Helper::fileUpload(
                        $validatedData[$field],
                        $field,
                        ($setting->name ?? 'system').time().'.'.$validatedData[$field]->getClientOriginalExtension()
                    );
                } else {
                    // Keep old file if no new upload
                    $validatedData[$field] = $setting->$field
                        ? str_replace(url('/').'/', '', $setting->$field)
                        : null;
                }
            }

            $setting->fill($validatedData)->save();

            return $setting;
        } catch (Exception $e) {
            Log::error('SystemSettingService::update - '.$e->getMessage());
            throw $e;
        }
    }

    public function getMailSetting()
    {
        try {
            $settings = [
                'mail_mailer' => env('MAIL_MAILER', ''),
                'mail_host' => env('MAIL_HOST', ''),
                'mail_port' => env('MAIL_PORT', ''),
                'mail_username' => env('MAIL_USERNAME', ''),
                'mail_password' => env('MAIL_PASSWORD', ''),
                'mail_encryption' => env('MAIL_ENCRYPTION', ''),
                'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
            ];

            return $settings;
        } catch (Exception $e) {
            Log::error('SystemSettingService::getMailSetting'.$e->getMessage());
            throw $e;
        }
    }

    public function updateMailSetting(array $validatedData)
    {
        try {
            $envPath = base_path('.env');

            if (! File::exists($envPath)) {
                throw new Exception('.env file not found.');
            }

            $envContent = File::get($envPath);
            $lineBreak = "\n";

            $mailKeys = [
                'mail_mailer' => 'MAIL_MAILER',
                'mail_host' => 'MAIL_HOST',
                'mail_port' => 'MAIL_PORT',
                'mail_username' => 'MAIL_USERNAME',
                'mail_password' => 'MAIL_PASSWORD',
                'mail_encryption' => 'MAIL_ENCRYPTION',
                'mail_from_address' => 'MAIL_FROM_ADDRESS',
            ];

            foreach ($mailKeys as $key => $envKey) {
                $value = $validatedData[$key] ?? null;

                // Keep old value if not provided
                if ($value === null) {
                    preg_match("/^{$envKey}=(.*)$/m", $envContent, $matches);
                    $value = $matches[1] ?? '';
                }

                // Always wrap MAIL_PASSWORD and MAIL_FROM_ADDRESS in quotes
                if (in_array($envKey, ['MAIL_PASSWORD', 'MAIL_FROM_ADDRESS'])) {
                    $value = '"'.trim($value, '"').'"';
                }

                // Replace or add
                if (preg_match("/^{$envKey}=.*/m", $envContent)) {
                    $envContent = preg_replace(
                        "/^{$envKey}=.*/m",
                        "{$envKey}={$value}",
                        $envContent
                    );
                } else {
                    $envContent .= "{$lineBreak}{$envKey}={$value}";
                }
            }

            File::put($envPath, $envContent);

            return true;
        } catch (Exception $e) {
            Log::error('SystemSettingService::updateMailSetting - '.$e->getMessage());
            throw $e;
        }
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('optimize:clear');
            Artisan::call('optimize');

            return true;
        } catch (Exception $e) {
            Log::error('SystemSettingService::clearCache'.$e->getMessage());
            throw $e;
        }
    }
}
