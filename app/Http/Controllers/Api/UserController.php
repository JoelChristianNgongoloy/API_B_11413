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
        $user = User::find($id);

        if (is_null($user)) {
            return response([
                'message' => 'User Not Found',
                'data' => null
            ], 400);
        }

        $updateData = $request->all();

        $validate = Validator::make($updateData, [
            'name' => 'max:60',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'min:8',
            'no_telp' => 'regex:/^08[0-9]{9,11}$/',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validate->fails())
            return response(['message' => $validate->errors()], 400);
        $user->name = $updateData['name'];
        $user->email = $updateData['email'];
        $user->password = $updateData['password'];
        $user->no_telp = $updateData['no_telp'];

        try {
            // Cek dan proses gambar jika ada di request
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalName();
                $image->move(public_path('images'), $imageName);
                $user->image = $updateData[$imageName];
            }

            $user->save();

            return response()->json([
                'message' => 'Update success',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
        }
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
