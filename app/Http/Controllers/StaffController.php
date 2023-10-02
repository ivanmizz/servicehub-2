<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\staff;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    //displaying all the staff in a table
    public function index()
    {
        $departmentList = Department::all();
        $staffList = Staff::with('department')->paginate(10); 
        return view('admin.add_staff', compact('departmentList', 'staffList'));
    }
    // adding employees
    public function addview()
    {
        return view('admin.add_staff');
    }

    public function create()
    {


    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10',
            'department' => 'required|exists:departments,id',
            'profession' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            // Adjust validation rules for image upload as needed
        ]);



        $staff = new staff;
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->phone = $request->phone;
        $staff->profession = $request->profession;
        $staff->department()->associate($request->department);


        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('staff_images', 'public'); // Store the image in the public disk's staff_images directory
            $staff->image = $imagePath;
        }


        $staff->save();

        return redirect()->back()->with('success', 'Employee added successfully.');
    }


    //     public function destroy($id)
// {
//     $staff = Staff::find($id);

    //     if (!$staff) {
//         return redirect()->back()->with('error', 'Staff member not found.');
//     }

    //     $staff->delete();

    //     return redirect()->route('staff_view.index')->with('success', 'Staff member removed successfully.');
// }
public function edit(Request $request, Staff $staff)
{
    // If request expects JSON (i.e., AJAX call), return the staff details.
    if ($request->expectsJson()) {
        return response()->json($staff);
    }

    // Retrieve the list of departments for the dropdown
    $departmentList = Department::all();
    return view('admin.staff_view', compact('staff', 'departmentList'));
}

    public function destroy(Staff $staff)
    {
        // Delete the staff record
        $staff->delete();

        // Optionally, delete the associated image file from storage
        if ($staff->image) {
            Storage::disk('public')->delete($staff->image);
        }

        return redirect()->route('staff_view.index')->with('success', 'Staff member deleted successfully.');
    }

    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'department' => 'required|exists:departments,id',
            'profession' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $staff->name = $request->input('name');
        $staff->email = $request->input('email');
        $staff->phone = $request->input('phone');
        $staff->department_id = $request->input('department');
        $staff->profession = $request->input('profession');

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($staff->image) {
                Storage::disk('public')->delete($staff->image);
            }
            // Store the new image
            $imagePath = $request->file('image')->store('staff_images', 'public');
            $staff->image = $imagePath;
        }
        $staff->save();

        return redirect()->route('staff_view.index')->with('success', 'Staff member updated successfully.');
    }


}