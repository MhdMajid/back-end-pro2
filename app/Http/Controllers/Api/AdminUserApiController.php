<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Validator;

class AdminUserApiController extends Controller
{
    
    public function index()
    {
        $users = User::all();
        return response()->json($users, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', 'in:admin,user'],
            'password' => ['required'],
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
            'role' => $validatedData['role'], 
            'password' => Hash::make($validatedData['password']),
        ]);

        return response()->json($user, Response::HTTP_CREATED);
    }

    
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user, Response::HTTP_OK);
    }

    
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email,'.$user->id],
            'role' => ['sometimes', 'required', 'string', 'in:admin,user'], // السماح بتحديث الدور
            'password' => ['nullable'], 
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->name = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->phone = $request->phone ?? $user->phone;
        $user->role = $request->role ?? $user->role; 
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return response()->json(["user" => $user]);
    }

    
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function toggleActive(string $id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(["is_active" => $user->is_active]);
    }
}
