<div style="font-family: Helvetica, Arial, sans-serif; min-width: 1000px; overflow: auto; line-height: 1.5; background-color: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
        <!-- Header Section -->
        <div style="text-align: center; border-bottom: 1px solid #eeeeee; padding-bottom: 10px;">
            <a href="" style="font-size: 1.5em; color: #00466a; text-decoration: none; font-weight: bold;">{{ $customMessage }}</a>
        </div>
        
        <!-- Greeting Section -->
        <p style="font-size: 1.1em; color: #333333; margin-top: 20px;">Hello, {{ $user->name }}</p>
        
        <!-- Body Section -->
        <p style="font-size: 1em; color: #555555; margin-top: 10px;">
            Thank you for choosing <strong>{{ config('app.name') }}</strong>. Use the following OTP to complete your sign-up process. The OTP is valid for <strong>1 hour</strong>.
        </p>
        
        <!-- OTP Section -->
        <div style="text-align: center; margin: 20px 0;">
            <h2 style="background-color: #00466a; color: #ffffff; display: inline-block; padding: 10px 20px; border-radius: 5px; margin: 0;">
                {{ $otp }}
            </h2>
        </div>
        
        <!-- Footer Section -->
        <p style="font-size: 0.9em; color: #555555; margin-top: 20px;">Best regards,<br>{{ config('app.name') }}</p>
        
        <hr style="border: none; border-top: 1px solid #eeeeee; margin: 20px 0;" />
        
        <!-- Company Info -->
        <div style="text-align: center; color: #aaaaaa; font-size: 0.8em; line-height: 1.4;">
            <p style="margin: 0;">{{ config('app.name') }} Inc.</p>
            {{-- <p style="margin: 0;">1600 Amphitheatre Parkway</p>
            <p style="margin: 0;">California</p> --}}
        </div>
    </div>
</div>
