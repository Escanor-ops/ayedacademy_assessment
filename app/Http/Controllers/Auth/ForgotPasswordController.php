<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'البريد الإلكتروني غير مسجل في النظام'
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Generate verification code
        $verificationCode = Str::random(6);
        
        // Save verification code and expiry
        $user->update([
            'verification_code' => $verificationCode,
            'verification_code_expires_at' => Carbon::now()->addMinutes(60)
        ]);

        // Send email
        Mail::send('emails.reset-password', ['code' => $verificationCode], function($message) use ($user) {
            $message->from('mosabry820@gmail.com', 'Tox')
                    ->to($user->email)
                    ->subject('رمز إعادة تعيين كلمة المرور');
        });

        return redirect()->route('password.reset', ['email' => $request->email])
                        ->with('success', 'تم إرسال رمز التحقق إلى بريدك الإلكتروني');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)
                    ->where('verification_code', $request->verification_code)
                    ->where('verification_code_expires_at', '>', Carbon::now())
                    ->first();

        if (!$user) {
            return back()->with('error', 'رمز التحقق غير صحيح أو منتهي الصلاحية');
        }

        $user->update([
            'password' => bcrypt($request->password),
            'verification_code' => null,
            'verification_code_expires_at' => null
        ]);

        return redirect()->route('login')
                        ->with('success', 'تم تحديث كلمة المرور بنجاح');
    }
}
