<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->role === 'super_manager') {
                return redirect('/admin/dashboard');
            } elseif ($user->role === 'manager') {
                return redirect('/manager/dashboard');
            } elseif ($user->role === 'department_manager') {
                return redirect('/department_manager/dashboard');
            } elseif ($user->role === 'employee') {
                return redirect('/employee/evaluation');
            } else {
                return redirect('/home'); // fallback
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role === 'super_manager') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'manager') {
                return redirect()->intended('/manager/evaluation');
            } elseif ($user->role === 'department_manager') {
                return redirect()->intended('/department_manager/evaluation');
            } elseif ($user->role === 'employee') {
                return redirect()->intended('/employee/evaluation');
            } else {
                return redirect()->intended('/home');
            }
        }

        return redirect()->back()->with('success', 'بيانات الدخول غير صحيحة');
    }

}
