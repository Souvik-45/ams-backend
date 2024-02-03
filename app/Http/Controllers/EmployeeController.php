<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Stevebauman\Location\Facades\Location;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\PersonalDetail;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function createDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Define a mapping of department names to department IDs
        $departmentIdMappings = [
            'IT' => '901',
            'Design' => '801',
            'Management' => '701',
            'Accounts' => '601',
            // Add more mappings as needed
        ];

        // Check if the provided department name is in the mapping
        if (!isset($departmentIdMappings[$request->name])) {
            return response()->json(['error' => 'Invalid department name'], 422);
        }

        // Create the department with the dynamically assigned department ID
        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'department_id' => $departmentIdMappings[$request->name],
        ]);


        return response()->json(['data' => $department], 200);
    }


    public function createLeave(Request $request)
    {
        // Validation rules for creating leave
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::user();

        $leave = Leave::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json(['data' => $leave], 200);
    }


// ...

public function updateAttendance(Request $request)
{
    $validator = Validator::make($request->all(), [
        // Remove 'location' from validation rules since it will be fetched automatically
        // 'location' => 'required|string',
        // Add other validation rules for attendance-related data
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Assume $user is the authenticated user, or retrieve it from the authentication
    $user = Auth::user();

    // Retrieve the last attendance record for the user
    $lastAttendance = $user->attendance()->latest()->first();

    if ($lastAttendance && $lastAttendance->status == 'in_office') {
        // If the user is already in the office
        $currentTime = Carbon::now();
        $lastCheckIn = $lastAttendance->check_in;
        $cooldownDuration = $lastCheckIn->addHours(4);

        if ($currentTime < $cooldownDuration) {
            // If the cooldown period is not over, return a message
            return response()->json([
                'error' => 'You can set the status to in_office again after the cooldown period (4 hours).'
            ], 403);
        }

        // If the cooldown period is over, update check-out time and set status to 'present'
        $lastAttendance->update([
            'check_out' => $currentTime,
            'status' => 'present',
        ]);

        return response()->json(['message' => 'Attendance updated successfully'], 200);
    } elseif ($lastAttendance && $lastAttendance->status == 'present') {
        // If the user's last status is 'present', check for inactivity and set 'absent' if needed
        $lastCheckIn = $lastAttendance->check_in;
        $currentTime = Carbon::now();
        $inactivityDuration = $currentTime->diffInHours($lastCheckIn);

        if ($inactivityDuration >= 20) {
            $lastAttendance->update([
                'status' => 'absent',
            ]);

            return response()->json(['message' => 'Attendance updated to absent due to inactivity'], 200);
        }
    }

    // Fetch the user's current location
    $currentLocation = Location::get();
    $latitude = $currentLocation->latitude;
    $longitude = $currentLocation->longitude;

    // If no previous record, create a new attendance record with the check-in time, location, and 'in_office' status
    $attendance = $user->attendance()->create([
        'check_in' => Carbon::now(),
        'latitude' => $latitude,
        'longitude' => $longitude,
        'status' => 'in_office',
    ]);

    return response()->json(['message' => 'Attendance recorded successfully'], 200);
}
public function getUserDetails(Request $request)
{
    $user = Auth::user();

    // Retrieve user's details from related tables
    $employeeDetails = Employee::where('user_id', $user->id)->first();
    $personalDetails = PersonalDetail::where('user_id', $user->id)->first();

    if (!$employeeDetails || !$personalDetails) {
        return response()->json(['error' => 'User details not found'], 404);
    }

    // You may add more details as needed from the 'users' table
    $userData = [
        'name' => $user->name,
        'email' => $user->email,
        'employee_details' => [
            'employee_id' => $employeeDetails->employee_id,
            'department_id' => $employeeDetails->department_id,
            'job_role' => $employeeDetails->job_role,
            'department_name' => $employeeDetails->department_name,
            // Add more employee details if needed
        ],
        'personal_details' => [
            'address' => $personalDetails->address,
            'mobileno' => $personalDetails->mobileno,
            'name' => $personalDetails->name,
            // Add more personal details if needed
        ],
        // Add more user details from the 'users' table if needed
    ];

    return response()->json(['data' => $userData], 200);
}

}
