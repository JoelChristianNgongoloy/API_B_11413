<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subscriptions = Subscription::with(['User'])->get();

        if (count($subscriptions) > 0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $subscriptions
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
            'category' => 'required|in:Basic,Standard,Premium'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        if (!in_array($storeData['category'], ['Basic', 'Standard', 'Premium'])) {
            return response([
                'message' => 'Invalid Category',
                'errors' => ['category' => ['Category must be Basic, Standard, or Premium']]
            ], 400);
        }

        $user = User::find($storeData['id_user']);

        if (!$user) {
            return response(['message' => 'User Not Found'], 400);
        }

        $user->update(['status' => 1]);

        switch ($storeData['category']) {
            case 'Basic':
                $storeData['price'] = 50000;
                break;
            case 'Standard':
                $storeData['price'] = 100000;
                break;
            case 'Premium':
                $storeData['price'] = 150000;
                break;
        }

        $subscription = new Subscription([
            'id_user' => $storeData['id_user'],
            'category' => $storeData['category'],
            'price' => $storeData['price'],
            'transaction_date' => now(),
        ]);

        $subscription->save();

        return response([
            'message' => 'Data stored successfully',
            'data' => $subscription
        ], 200);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $subscriptions = Subscription::find($id);
        if (!is_null($subscriptions)) {
            return response([
                'message' => 'Subcriptions Found',
                'data' => $subscriptions
            ], 200);
        }

        return response([
            'message' => 'Subcriptions not Found',
            'data' => null
        ], 400);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $updateData = $request->all();

        $validate = Validator::make($updateData, [
            'id_user' => 'required',
            'category' => 'required|in:Basic,Standard,Premium'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        if (!in_array($updateData['category'], ['Basic', 'Standard', 'Premium'])) {
            return response([
                'message' => 'Invalid Category',
                'errors' => ['category' => ['Category must be Basic, Standard, or Premium']]
            ], 400);
        }

        $subscription = Subscription::find($id);

        if (!$subscription) {
            return response(['message' => 'Subscription Not Found'], 404);
        }

        $user = User::find($updateData['id_user']);

        if (!$user) {
            return response(['message' => 'User Not Found'], 400);
        }

        // Check if the user is active
        if ($user->status !== 1) {
            return response([
                'message' => 'User Not Active',
                'errors' => ['id_user' => ['User must be active']]
            ], 400);
        }

        // Set the price based on the category
        switch ($updateData['category']) {
            case 'Basic':
                $updateData['price'] = 50000;
                break;
            case 'Standard':
                $updateData['price'] = 100000;
                break;
            case 'Premium':
                $updateData['price'] = 150000;
                break;
        }

        // Optionally, update the transaction_date
        $updateData['transaction_date'] = now();

        // Update subscription data
        $subscription->update([
            'id_user' => $updateData['id_user'],
            'category' => $updateData['category'],
            'price' => $updateData['price'],
            'transaction_date' => $updateData['transaction_date'],
        ]);

        return response([
            'message' => 'Data updated successfully',
            'data' => $subscription
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $subscriptions = Subscription::find($id);
        if (is_null($subscriptions)) {
            return response([
                'message' => 'Subscriptions Not Found',
                'data' => null
            ], 400);
        }

        if ($subscriptions->delete()) {
            return response([
                'message' => 'Delete Subscription Success',
                'data' => $subscriptions
            ], 200);
        }
        return response([
            'message' => 'Delete Subscription Fail',
            'data' => null
        ], 400);
    }
}
