<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $products = $request->input('products');
        $userId = $request->input('userId');

        if ($products === null) {
            return response()->json(['message' => 'Products not found'], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $order = new Order([
            'status' => 'pending',
            'date' => now(),
            'number' => uniqid(),
            'user_id' => $user->id
        ]);

        $order->save();

        foreach ($products as $productId => $quantity) {
            $product = Product::where('id', $productId)->first();

            if (!$product) {
                $order->delete();
                return response()->json(['message' => "'{$productId}' not found"], 404);
            }

            if ($product->stock < $quantity) {
                $order->delete();
                return response()->json(['message' => "'{$product->name}' out of stock"], 400);
            }

            $orderItem = new OrderItem([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price
            ]);

            $order->items()->save($orderItem);
        }

        foreach ($products as $productId => $quantity) {
            $product = Product::where('id', $productId)->first();
            $product->stock -= $quantity;
            $product->save();
        }

        return response()->json(['message' => 'Order created successfully'], 201);
    }

    public function approve(Request $request)
    {
        $userId = $request->input('userId');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $order = Order::where('user_id', $userId)->where('status', 'pending')->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $balance = $user->balance;
        $orderItems = $order->items;
        $totalPrice = 0;

        foreach ($orderItems as $orderItem) {
            $totalPrice += $orderItem->price * $orderItem->quantity;
        }
        
        if($balance < $totalPrice){
            return response()->json(['message' => 'Insufficient funds'], 400);
        }

        $user->balance -= $totalPrice;
        $user->save();
        $order->status = 'approved';
        $order->save();

        return response()->json(['message' => 'Order approved successfully'], 201);
    }
}
