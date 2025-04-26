<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        DB::beginTransaction();

        try {
            $cart = Cart::with('items.product')
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El carrito está vacío o no existe'
                ], 400);
            }

            $outOfStockProducts = [];
            foreach ($cart->items as $cartItem) {
                if ($cartItem->product->stock < $cartItem->quantity) {
                    $outOfStockProducts[] = [
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product->name,
                        'available_stock' => $cartItem->product->stock,
                        'requested_quantity' => $cartItem->quantity
                    ];
                }
            }

            if (!empty($outOfStockItems)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Algunos productos no tienen suficiente stock',
                    'out_of_stock_items' => $outOfStockProducts
                ], 400);
            }

            $total = $cart->items->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $order = Order::create([
                'user_id' => Auth::id(),
                'total' => $total
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'price_at_purchase' => $cartItem->product->price,
                    'quantity' => $cartItem->quantity
                ]);

                $cartItem->product->decrement('stock', $cartItem->quantity);
            }

            $cart->items()->delete();
            $cart->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Compra finalizada correctamente',
                'order' => $order->load('items.product'),
                'new_stock' => $order->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'new_stock' => $item->product->fresh()->stock
                    ];
                })
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al finalizar la compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
