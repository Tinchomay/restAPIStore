<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ], [
            'product_id.required' => 'El ID del producto es obligatorio',
            'product_id.exists' => 'El producto no existe',
            'quantity.required' => 'La cantidad es obligatoria',
            'quantity.integer' => 'La cantidad debe ser un número entero',
            'quantity.min' => 'La cantidad mínima es 1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $cart = Cart::firstOrCreate(
                ['user_id' => Auth::id(), 'status' => 'pending'],
                ['status' => 'pending']
            );

            $cartItem = $cart->items()
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $validated['quantity']
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity']
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Producto agregado al carrito',
                'cart' => $cart->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al agregar producto al carrito',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function removeItem(Request $request, $productId)
    {
        $validator = Validator::make(['product_id' => $productId], [
            'product_id' => 'required|exists:products,id'
        ], [
            'product_id.required' => 'El ID del producto es obligatorio',
            'product_id.exists' => 'El producto no existe'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cart = Cart::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if (!$cart) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No hay carrito activo'
                ], 404);
            }

            $deleted = $cart->items()
                ->where('product_id', $productId)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Producto eliminado del carrito'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'El producto no estaba en el carrito'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar producto del carrito',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
