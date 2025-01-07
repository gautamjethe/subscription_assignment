<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'product_name' => 'required|string|max:255',
            'frequency' => 'required|in:daily,weekly',
            'quantity' => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0.01',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        $subscription = Subscription::create($validated);

        return response()->json(['status' => 'success', 'message' => 'Subscription created successfully.', 'data' => $subscription], 200);
    }

    public function update(Request $request, $subscription_id)
    {
        $subscription = Subscription::find($subscription_id);

        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found.'], 404);
        }

        if ($subscription->end_date < now()->toDateString()) {
            return response()->json(['error' => 'Cannot modify inactive subscriptions.'], 400);
        }

        $validated = $request->validate([
            'frequency' => 'nullable|in:daily,weekly',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $subscription->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Subscription updated successfully.', 'data' => $subscription]);
    }

    // public function cancel($subscription_id)
    // {
    //     $subscription = Subscription::find($subscription_id);

    //     if (!$subscription) {
    //         return response()->json(['error' => 'Subscription not found.'], 404);
    //     }

    //     if ($subscription->end_date < now()->toDateString()) {
    //         return response()->json(['error' => 'Cannot cancel inactive subscriptions.'], 400);
    //     }

    //     $subscription->delete();

    //     return response()->json(['status' => 'success', 'message' => 'Subscription canceled successfully.']);
    // }

    public function cancel($subscription_id)
{
    $subscription = Subscription::find($subscription_id);

    if (!$subscription) {
        return response()->json(['error' => 'Subscription not found.'], 404);
    }

    if ($subscription->end_date < now()->toDateString()) {
        return response()->json(['error' => 'Cannot cancel inactive subscriptions.'], 400);
    }

    $createdDate = $subscription->created_at;
    $currentDate = now();

    $cancellationFee = 0;

    
    if ($createdDate->diffInDays($currentDate) <= 7) {
        
        $cancellationFee = 50; 
    }

    $subscription->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Subscription cancelled successfully.',
        'cancellation_fee' => $cancellationFee,
    ]);
}


    public function viewSubscriptions($customer_id)
    {
        $subscriptions = Subscription::where('customer_id', $customer_id)->get();

        return response()->json(['customer_id' => $customer_id, 'subscriptions' => $subscriptions]);
    }
}