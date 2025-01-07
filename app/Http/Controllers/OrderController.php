<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderUpdate;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    
    public function placeOrder(Request $request)
    {
        $validatedData = $request->validate([
            'order_id' => 'required|integer|unique:orders',
            'customer_id' => 'required|integer|exists:customers,id',
            'product_id' => 'required|integer|exists:products,id',
            'product_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01',
            'order_date' => 'required|date',
        ]);

        
        $product = Product::find($validatedData['product_id']);
        if ($product->stock < $validatedData['quantity']) {
            return response()->json(['error' => 'Insufficient stock available'], 400);
        }

        // Deduct stock and create order
        $product->update(['stock' => $product->stock - $validatedData['quantity']]);
        Order::create(array_merge($validatedData, ['status' => 'placed']));
        return response()->json([
            'status' => 'success',
            'message' => 'Order placed successfully.'
        ]);
    }

    
    public function modifyOrder(Request $request, $order_id)
    {
        $order = Order::find($order_id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if (in_array($order->status, ['delivered', 'canceled'])) {
            return response()->json(['error' => 'Modification not allowed for delivered or canceled orders'], 400);
        }

        $customer = Customer::find($order->customer_id);
        if ($customer->subscription_end_date && $order->order_date < now()->toDateString()) {
            return response()->json(['error' => 'Cannot modify past orders for subscription customers.'], 400);
        }

        

        $validatedData = $request->validate([
            'quantity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0.01',            
        ]);
        

        if (isset($validatedData['quantity'])) {
            $product = Product::find($order->product_id);
            $stockChange = $validatedData['quantity'] - $order->quantity;

            if ($product->stock < $stockChange) {
                return response()->json(['error' => 'Insufficient stock available for modification'], 400);
            }

            
            $product->update(['stock' => $product->stock - $stockChange]);
        }

        //$order->update($validatedData);
        $oldData = $order->toArray();
        $order->update($validatedData);

        OrderUpdate::create([
            'order_id' => $order_id,
            'changes' => json_encode(['before' => $oldData, 'after' => $order->toArray()]),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Order updated successfully.'
        ]);
    }

    
    public function cancelOrder($order_id)
    {
        $order = Order::find($order_id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order->status == 'delivered') {
            return response()->json(['error' => 'Cannot cancel delivered orders'], 400);
        }

        $cancellationFee = 0;
        if ($order->status == 'processing') {
            $cancellationFee = $order->price * 0.05;
        }

        
        $product = Product::find($order->product_id);
        $product->update(['stock' => $product->stock + $order->quantity]);

        $order->update(['status' => 'canceled']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order canceled successfully.',
            'cancellation_fee' => $cancellationFee
        ]);
    }

    
    public function calculateTotal($customer_id)
    {
        $customer = Customer::find($customer_id);
        if (!$customer) {
            return response()->json([
                'error' => "No customer found with ID {$customer_id}"
            ], 404);
        }
        $orders = Order::where('customer_id', $customer_id)
                        ->where('status', '!=', 'canceled')
                        ->get();

        if ($orders->isEmpty()) {
            return response()->json(['error' => 'No active orders found'], 404);
        }

        $totalAmount = $orders->sum(fn($order) => $order->quantity * $order->price);
        $discount = $totalAmount > 5000 ? 0.10 : 0;
        $finalTotal = $totalAmount - ($totalAmount * $discount);

        return response()->json([
            'customer_id' => $customer_id,
            'total_amount' => $totalAmount,
            'discount_applied' => $discount > 0 ? '10%' : '0%',
            'final_total' => $finalTotal
        ]);
    }
}