<?php

namespace App\Http\Controllers\SuperManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    /**
     * Display a listing of managers.
     */
    public function index()
    {
        $managers = User::where('role', 'manager')->get();
        return view('super.managers.index', compact('managers'));
    }

    /**
     * Show the form for creating a new manager.
     */
    public function create()
    {
        return view('super.managers.create');
    }

    /**
     * Store a newly created manager in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'position' =>'manager',
            'password' => Hash::make($request->password),
            'role'     => 'manager',
            'status'        => 0
        ]);

        return redirect()->route('admin.managers.index')->with('success', 'تم اضافة المدير بنجاح');
    }

    /**
     * Show the form for editing a manager.
     */
    public function edit($id)
    {
        $manager = User::findOrFail($id);
        return view('admin.managers.index', compact('manager'));
    }

   
    public function update(Request $request, $id)
    {

        $manager = User::findOrFail($id);
        
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password'     => 'nullable|min:6',
        ]);

        if($request->status == 1) {
            $duplicateActive = User::where('id', '!=', $id)
                ->where('role', $manager->role)
                ->where('status', 1)
                ->first();
    
            if ($duplicateActive) {
                return redirect()->route('admin.managers.index')->with('failed', 'يوجد مدير نشط بالفعل');
            }
        }
        $data = [
            'name'          => $request->name,
            'email'         => $request->email,
            'status'     => $request->status,

        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $manager->update($data);

        return redirect()->route('admin.managers.index')->with('success', 'تم تعديل بيانات المدير بنجاح');
    }

    /**
     * Remove the specified manager.
     */
    public function destroy($id)
    {
        $manager = User::findOrFail($id);
        $manager->delete();

        return redirect()->route('admin.managers.index')->with('success', 'تم حذف المدير بنجاح');
    }
}
