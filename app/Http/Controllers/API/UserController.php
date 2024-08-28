<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{


    /**
     * Display a listing of the users.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Initialize the query builder
            $query = User::with('role');

            // Dynamically apply filters
            foreach ($request->all() as $field => $value) {
                if (Schema::hasColumn('users', $field)) {
                    $query->where($field, 'like', "%{$value}%");
                }
            }

            // Check if the per_page parameter is set
            if ($request->has('per_page')) {
                $perPage = intval($request->get('per_page'));
                $users = $query->paginate($perPage);
            } else {
                // If per_page is not set, get all users
                $users = $query->get();
            }

            return $this->sendResponse($users, 'Users retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving users.', ['error' => $e->getMessage()]);
        }
    }


    /**
     * Get the authenticated User
     */
    public function showProfile()
    {
        $user = Auth::user()->load('role');
        return $this->sendResponse($user->toArray(), 'User profile retrieved successfully.');
    }


    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            'phone' => 'sometimes|required|string|unique:users,phone,' . Auth::id(),
            'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:512',
        ]);

        // Handle validation errors
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Retrieve authenticated user
        $user = Auth::user();

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo) {
                Storage::delete('public/avatars/' . $user->photo);
            }

            // Store new photo
            $photoName = time() . '.' . $request->photo->extension();
            $request->photo->storeAs('public/avatars', $photoName);
            $user->photo = $photoName;
        }

        // Update user information
        $user->update($request->only(['name', 'email', 'phone']));

        return $this->sendResponse($user, 'Profile updated successfully.');
    }

    /**
     * Retrieve the groups of the authenticated student.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentGroups()
    {
        // Load only the groups along with main and assistant teachers for the authenticated student
        $user = Auth::user()->load([
            'groups.mainTeacher', 
            'groups.assistantTeacher', 
            'groups.students', 
            'groups.subject',   
            'groups.classes.day',
            'groups.classes.room'
        ]);

        // You can customize the response to return only the group data if desired
        $groups = $user->groups;

        return $this->sendResponse($groups, 'Student groups retrieved successfully.');
    }
}
