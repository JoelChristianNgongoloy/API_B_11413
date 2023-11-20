<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Activities;
use App\Models\Content;

class ActivitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $activities = Activities::with(['User', 'Content'])->get();

        if (count($activities) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $activities
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
        $storeData = $request->all();

        $validate = Validator::make($storeData, [
            'id_user' => 'required',
            'id_content' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user = User::find($storeData['id_user']);
        if (!$user) {
            return response(['message' => 'User not found'], 400);
        }

        if ($user->status !== 1) {
            return response(['message' => 'User is not active'], 400);
        }

        $content = Content::find($storeData['id_content']);
        if (!$content) {
            return response(['message' => 'Content not found'], 400);
        }

        if ($content->type === 'Paid' && !$user->isSubscribed()) {
            return response(['message' => 'User is not subscribed to access paid content'], 400);
        }

        $activities = Activities::create([
            'id_user' => $storeData['id_user'],
            'id_content' => $storeData['id_content'],
            'accessed_at' => now(),
        ]);

        return response([
            'message' => $user->name . ' accessed ' . $content->title . ' at ' . $activities['accessed_at'] . '.',
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $activities = Activities::find($id);
        if (!is_null($activities)) {
            return response([
                'message' => 'Activities Found',
                'data' => $activities
            ], 200);
        }

        return response([
            'message' => 'Activities Not Found',
            'data' => null
        ], 400);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $updateData = $request->all();
        $activities = Activities::find($id);

        if (is_null($activities)) {
            return response([
                'message' => 'Activities not found',
                'data' => null
            ], 400);
        }

        $validate = Validator::make($updateData, [
            'id_user' => 'required',
            'id_content' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        $user = User::find($updateData['id_user']);
        if (!$user) {
            return response(['message' => 'User not found'], 400);
        }

        dd($user->isSubscribed());

        if ($user->status !== 1) {
            return response(['message' => 'User is not active'], 400);
        }

        $content = Content::find($updateData['id_content']);
        if (!$content) {
            return response(['message' => 'Content not found'], 400);
        }

        if ($content->type === 'Paid' && !$user->isSubscribed()) {
            return response(['message' => 'User is not subscribed to access paid content'], 400);
        }

        $activities->id_user = $updateData['id_user'];
        $activities->id_content = $updateData['id_content'];
        $activities->accessed_at = now();

        if ($activities->save()) {
            return response([
                'message' => 'Activities updated successfully',
                'data' => $activities
            ], 200);
        }

        return response([
            'message' => 'Failed to update activities',
            'data' => null
        ], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $activities = Activities::find($id);
        if (is_null($activities)) {
            return response([
                'message' => 'Activities Not Found',
                'data' => null
            ], 400);
        }

        if ($activities->delete()) {
            return response([
                'message' => 'Delete Activities Succes',
                'data' => $activities
            ], 200);
        }
        return response([
            'message' => 'Delete Activities Fail',
            'data' => null
        ], 400);
    }
}
