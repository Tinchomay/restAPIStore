<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stores = Store::all();

        return response()->json($stores);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ], [
            'name.required' => 'El nombre de la tienda es obligatorio',
            'name.max' => 'El nombre no debe exceder 255 caracteres',
            'description.string' => 'La descripción debe ser texto válido'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $validatedData = $validator->validated();
            $store = Store::create([
                'name' => $validatedData['name'],
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'store' => [
                        'id' => $store->id,
                        'name' => $store->name,
                        'seller_id' => $store->user_id,
                    ]
                ],
                'message' => 'Tienda creada correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear la tienda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $store = Store::find($id);

        return $store
            ? response()->json($store)
            : response()->json(['message' => 'Tienda no encontrada'], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Actualiza una tienda específica
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $store = Store::findOrFail($id);

            if ($store->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Solo el dueño puede modificar la tienda'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255|required'
            ], [
                'name.string' => 'El nombre debe ser texto',
                'name.max' => 'El nombre no puede exceder 255 caracteres',
                'name.required' => 'El nombre es obligatorio'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $store->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'user_id' => $store->user_id
                ],
                'message' => 'Tienda actualizada correctamente'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tienda no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar la tienda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Elimina una tienda específica
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $store = Store::findOrFail($id);

            if ($store->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No autorizado. Solo el dueño puede eliminar la tienda'
                ], 403);
            }

            $store->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Tienda eliminada correctamente',
                'data' => [
                    'deleted_id' => $id,
                    'deleted_at' => now()->toDateTimeString()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tienda no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar la tienda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStoreSales(Request $request)
    {
        try {
            $stores = Store::with(['products' => function ($query) {
                $query->with(['orderItems' => function ($query) {
                    $query->with('order.user');
                }]);
            }])
            ->where('user_id', Auth::id())
            ->get();

            $salesData = $stores->map(function ($store) {
                $totalSales = 0;
                $totalItemsSold = 0;
                $orders = [];

                foreach ($store->products as $product) {
                    foreach ($product->orderItems as $orderItem) {
                        $totalSales += $orderItem->price_at_purchase * $orderItem->quantity;
                        $totalItemsSold += $orderItem->quantity;

                        $orders[$orderItem->order_id] = $orderItem->order;
                    }
                }

                return [
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'total_products' => $store->products->count(),
                    'total_sales' => $totalSales,
                    'total_items_sold' => $totalItemsSold,
                    'total_orders' => count($orders),
                    'orders' => array_values($orders)
                ];
            });

            return response()->json([
                'stores' => $salesData,
                'total_stores' => $stores->count(),
                'grand_total_sales' => $salesData->sum('total_sales')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las ventas por tienda',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
