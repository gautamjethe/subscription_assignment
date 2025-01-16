<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function rateProduct(Request $request, $productId)
{
    $user = $request->user();

    $validated = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
    ]);

    
    $existingRating = Rating::where('user_id', $user->id)
        ->where('product_id', $productId)
        ->first();

    if ($existingRating) {
        return response()->json([
            'error' => 'You have already rated this product.',
        ], 400);
    }

    
    Rating::create([
        'user_id' => $user->id,
        'product_id' => $productId,
        'rating' => $validated['rating'],
    ]);

    return response()->json([
        'message' => 'Product rated successfully.',
    ], 200);
}

public function updateRating(Request $request, $productId)
{
    $user = $request->user();

    $validated = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
    ]);

    
    $existingRating = Rating::where('user_id', $user->id)
        ->where('product_id', $productId)
        ->first();

    if (!$existingRating) {
        return response()->json([
            'error' => 'You have not rated this product yet.',
        ], 404);
    }

    
    $existingRating->update([
        'rating' => $validated['rating'],
    ]);

    return response()->json([
        'message' => 'Rating updated successfully.',
    ]);
}



public function getProductRating($productId)
{
    $product = Product::with('ratings')->find($productId);

    if (!$product) {
        return response()->json([
            'error' => 'Product not found.',
        ], 404);
    }

    $averageRating = $product->ratings()->avg('rating');

    return response()->json([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'average_rating' => round($averageRating, 2),
    ]);
}

}