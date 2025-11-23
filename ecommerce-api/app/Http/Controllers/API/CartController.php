<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $items = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($data['product_id']);
        $cartItem = CartItem::updateOrCreate(
            [
                'user_id'    => $user->id,
                'product_id' => $product->id,
            ],
            [
                'quantity'   => $data['quantity'],
            ]
        );

        return response()->json($cartItem, 201);
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $user = $request->user();

        if ($cartItem->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Cart item removed']);
    }
}
