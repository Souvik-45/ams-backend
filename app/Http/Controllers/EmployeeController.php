<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Stevebauman\Location\Facades\Location;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\PersonalDetail;
use App\Models\Attendance;
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

        $departmentIdMappings = [
            'IT' => '901',
            'Design' => '801',
            'Management' => '701',
            'Accounts' => '601',
        ];

        if (!isset($departmentIdMappings[$request->name])) {
            return response()->json(['error' => 'Invalid department name'], 422);
        }

        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'department_id' => $departmentIdMappings[$request->name],
        ]);

        return response()->json(['data' => $department], 200);
    }

    public function createLeave(Request $request)
    {
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

    public function updateAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }


        $user = Auth::user();
        $lastAttendance = $user->attendance()->latest()->first();

        $image = $request->file('image');
        $imageName = $image->getClientOriginalName();
        $imagePath = $image->storeAs('attendances', $imageName);

        if ($lastAttendance && $lastAttendance->status == 'in_office') {
            $currentTime = Carbon::now();
            $lastCheckIn = Carbon::parse($lastAttendance->check_in)->addHours(4);

            if ($currentTime < $lastCheckIn) {
                return response()->json(['error' => 'You can set the status to in_office again after the cooldown period (4 hours).'], 403);
            }

            $lastAttendance->update([
                'check_out' => $currentTime,
                'status' => 'present',
                'image_name' => $imageName,
            ]);

            return response()->json(['message' => 'Attendance updated successfully'], 200);
        } elseif ($lastAttendance && $lastAttendance->status == 'present') {
            $lastCheckIn = Carbon::parse($lastAttendance->check_in);
            $currentTime = Carbon::now();
            $inactivityDuration = $currentTime->diffInHours($lastCheckIn);

            if ($inactivityDuration >= 20) {
                $lastAttendance->update([
                    'status' => 'absent',
                    'image_name' => $imageName,
                ]);

                return response()->json(['message' => 'Attendance updated to absent due to inactivity'], 200);
            }
        }

        $currentLocation = Location::get();
        $latitude = $currentLocation->latitude;
        $longitude = $currentLocation->longitude;

        $attendance = $user->attendance()->create([
            'check_in' => Carbon::now(),
            'location' => $latitude . $longitude,
            'status' => 'in_office',
            'image_name' => $imageName,
            'image_location' => $imagePath,
        ]);

        return response()->json(['message' => 'Attendance recorded successfully'], 200);
    }

    public function getUserDetails(Request $request)
    {
        $user = Auth::user();
        $employeeDetails = Employee::where('user_id', $user->id)->first();
        $personalDetails = PersonalDetail::where('user_id', $user->id)->first();

        if (!$employeeDetails || !$personalDetails) {
            return response()->json(['error' => 'User details not found'], 404);
        }

        $userData = [
            'name' => $user->name,
            'email' => $user->email,
            'employee_details' => [
                'employee_id' => $employeeDetails->employee_id,
                'department_id' => $employeeDetails->department_id,
                'job_role' => $employeeDetails->job_role,
                'department_name' => $employeeDetails->department_name,
            ],
            'personal_details' => [
                'address' => $personalDetails->address,
                'mobileno' => $personalDetails->mobileno,
                'name' => $personalDetails->name,
            ],
        ];

        return response()->json(['data' => $userData], 200);
    }
}

?>
