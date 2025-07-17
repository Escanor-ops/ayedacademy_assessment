<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'تم تحديث كلمة المرور بنجاح');
    }
}
