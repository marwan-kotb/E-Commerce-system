<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order->load('items.product');

        return response()->json($order);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
        ]);

        $cartItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        $errors = [];

        foreach ($cartItems as $item) {
            if (!$item->product) {
                $errors[] = "Product not found for cart item ID {$item->id}";
                continue;
            }

            if ($item->product->stock < $item->quantity) {
                $errors[] = "Not enough stock for product '{$item->product->name}'";
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Stock validation failed',
                'errors' => $errors,
            ], 400);
        }

        $orderSummary = null;

        DB::transaction(function () use ($user, $data, $cartItems, &$orderSummary) {
            $total = 0;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . Str::upper(Str::random(8)),
                'address' => $data['address'],
                'phone' => $data['phone'],
                'total' => 0,
                'status' => 'pending',
            ]);

            $itemsSummary = [];

            foreach ($cartItems as $item) {
                $product = $item->product;
                $price = $product->price;
                $subtotal = $price * $item->quantity;
                $total += $subtotal;

                $product->stock -= $item->quantity;

                if ($product->stock <= 0) {
                    $product->stock = 0;
                    $product->status = 'out_of_stock';
                }

                $product->save();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id'=> $product->id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $itemsSummary[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $order->total = $total;
            $order->status = 'completed';
            $order->save();

            CartItem::where('user_id', $user->id)->delete();

            $orderSummary = [
                'order_number' => $order->order_number,
                'total' => $order->total,
                'items' => $itemsSummary,
            ];
        });

        return response()->json($orderSummary, 201);
    }
}
