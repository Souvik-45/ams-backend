<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Employee;
use App\Models\PersonalDetail;


use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Facades\Passport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8',
        'user_type' => 'required|in:employee,sub-admin', // Validate user type
        'job_role' => 'required|string', // Validate job role
        'address' => 'required|string', // Validate address
        'mobileno' => 'required|string', // Validate mobile number
        'department' => 'required|in:IT,Design,Management,Accounts', // Validate department
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    try {
        $lastUser = User::latest()->first();
        $nextEmployeeId = $lastUser ? str_pad((int)substr($lastUser->employee_id, 3) + 1, 5, '0', STR_PAD_LEFT) : '00001';

        $departmentIdMappings = [
            'IT' => '901',
            'Design' => '801',
            'Management' => '701',
            'Accounts' => '601',
        ];

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->user_type,
            'employee_id' => 'EMP' . $nextEmployeeId,
            'department_id' => $departmentIdMappings[$request->department],
        ]);

        if ($user) {
            // Create associated records in other tables
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_name' => $user->name,
                'employee_id' => $user->employee_id,
                'department_id' => $user->department_id,
                'job_role' => $request->job_role,
                'department_name' => $request->department,
            ]);

            $personalDetail = PersonalDetail::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'mobileno' => $request->mobileno,
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return response()->json(['Success login' => 'Authorized'], 200);
        } else {

            return response()->json(['error' => 'Something went wrong'], 500);
        }
    } catch (\Exception $e) {
        dd($e->getMessage());
        Log::error('Error during registration: ' . $e->getMessage());
        return response()->json(['error' => 'Something went wrong'], 400);
    }
}



     public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('AppName')->accessToken;

                return response()->json(['success' => 'authorized', 'token' => $token, 'user' => $user], 200);


            }
            else{

            return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (\Exception $e) {
            \Log::error('Error during login: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}

