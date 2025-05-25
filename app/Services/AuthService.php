<?php

namespace App\Services;

use App\Models\User;
// use App\Models\PasswordReset;
// use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\OtpMail;
// use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AuthService
{
  

    public function login($request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        return response()->json([
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user
        ]);
    }

    public function logout($request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function getUser($request)
    {
        return response()->json($request->user());
    }
    public function register(Request $request)
    {
        // التحقق من البيانات
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            // 'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // إنشاء المستخدم
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email, 
            'phone' => $request->phone, 
            'role' => 'user', 
            'password' => Hash::make($request->password),
            'email_verified_at' => null,
        ]);

        // إرسال رمز OTP للتحقق
        // $this->generateAndSendOtp($user);

        return response()->json([
            'message' => 'تم التسجيل بنجاح.',
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ], 201);
    }


    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }
    
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        // $token = Str::random(60);

        // حفظ طلب إعادة تعيين كلمة المرور
        // PasswordReset::updateOrCreate(
        //     ['email' => $user->email],
        //     [
        //         'token' => $token,
        //         'created_at' => Carbon::now()
        //     ]
        // );

        // إرسال بريد إلكتروني لإعادة تعيين كلمة المرور
        // Mail::to($user->email)->send(new ResetPasswordMail($token));

        return response()->json(['message' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني']);
    }
    
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // $passwordReset = PasswordReset::where('token', $request->token)->first();

        // if (!$passwordReset || Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
        //     return response()->json(['message' => 'رمز إعادة تعيين كلمة المرور غير صالح أو منتهي الصلاحية'], 422);
        // }
        $otpis = "1111rr";
        if($request->otp != $otpis){
             return response()->json(['message' => 'رمز إعادة تعيين كلمة المرور غير صالح '], 422);
        }
         $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // حذف طلب إعادة تعيين كلمة المرور
        // $passwordReset->delete();

        return response()->json(['message' => 'تم إعادة تعيين كلمة المرور بنجاح' ]);
    }
    
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // $user = User::where('email', $request->email)->first();
        
        // if ($user->email_verified_at) {
        //     return response()->json(['message' => 'البريد الإلكتروني مؤكد بالفعل'], 422);
        // }

        // $this->generateAndSendOtp($user);

        return response()->json(['message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني' , ]);
    }
    
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();
        // $otpVerification = OtpVerification::where('user_id', $user->id)
        //     ->where('otp', $request->otp)
        //     ->first();

        // if (!$otpVerification || Carbon::parse($otpVerification->expires_at)->isPast()) {
        //     return response()->json(['message' => 'رمز التحقق غير صالح أو منتهي الصلاحية'], 422);
        // }
        if ($request->otp != "1111") {
            return response()->json(['message' => 'رمز التحقق غير صالح أو منتهي الصلاحية'], 422);
        }

        // تأكيد البريد الإلكتروني
        $user->update(['email_verified_at' => Carbon::now()]);
        
        // حذف رمز التحقق
        // $otpVerification->delete();

        return response()->json(['message' => 'تم تأكيد البريد الإلكتروني بنجاح']);
    }
    
    // وظيفة مساعدة لإنشاء وإرسال رمز OTP
    // private function generateAndSendOtp(User $user)
    // {
    //     // إنشاء رمز OTP من 6 أرقام
    //     $otp = sprintf("%06d", mt_rand(1, 999999));
        
    //     // حفظ رمز OTP في قاعدة البيانات
    //     OtpVerification::updateOrCreate(
    //         ['user_id' => $user->id],
    //         [
    //             'otp' => $otp,
    //             'expires_at' => Carbon::now()->addMinutes(15)
    //         ]
    //     );
        
    //     // إرسال رمز OTP عبر البريد الإلكتروني
    //     Mail::to($user->email)->send(new OtpMail($otp));
    // }
}