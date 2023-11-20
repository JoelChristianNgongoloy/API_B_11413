<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        if (count($users) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $users
            ], 200);
        }

        return response([
            'message' => 'empty',
            'data' => null
        ], 400);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $registrationData = $request->all();

        $validate = Validator::make($registrationData, [
            'name' => 'required|max:60',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required|min:8',
            'no_telp' => 'required|regex:/^08[0-9]{9,11}$/',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048' // Menambahkan validasi untuk gambar
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $registrationData['status'] = 0;
        $registrationData['password'] = bcrypt($request->password);
        $registrationData['id'] = User::generateUserId();

        // Cek apakah ada file gambar di request
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $registrationData['image'] = $imageName;
        }

        $user = User::create($registrationData);

        return response([
            'message' => 'Register Success',
            'user' => $user
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $users = User::find($id);

        if (!is_null($users)) {
            return response([
                'message' => 'user found',
                'data' => $users
            ], 200);
        }

        return response([
            'message' => 'user Not Found',
            'data' => null

        ], 400);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the user by ID
        $user = User::find($id);

        // Check if the user exists
        if (!$user) {
            return response(['message' => 'User not found'], 404);
        }

        // Validate the incoming request data
        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'name' => 'sometimes|required|max:60',
            'email' => 'sometimes|required|email:rfc,dns|unique:users,email,' . $id,
            'password' => 'sometimes|required|min:8',
            'no_telp' => 'sometimes|required|regex:/^08[0-9]{9,11}$/',
            'image' => 'sometimes|required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        // Update user data
        $user->name = $request->has('name') ? $request->name : $user->name;
        $user->email = $request->has('email') ? $request->email : $user->email;
        $user->no_telp = $request->has('no_telp') ? $request->no_telp : $user->no_telp;

        // Update password if provided
        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        // Update image if provided
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $user->image = $imageName;
        }

        // Save the updated user
        $user->save();

        return response([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            return response([
                'message' => 'User Not Found',
                'data' => null
            ], 400);
        }
        if ($user->delete()) {
            return response([
                'message' => 'Delete User Success',
                'data' => $user
            ], 200);
        }
        return response([
            'message' => 'Delete User Failed',
            'data' => null
        ], 400);
    }
}
