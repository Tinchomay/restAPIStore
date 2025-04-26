<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Store $store)
    {
        $products = $store->products()->get();

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Store $store)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string'
        ], [
            'name.required' => 'El nombre del producto es obligatorio',
            'name.max' => 'El nombre no debe exceder 255 caracteres',
            'price.required' => 'El precio es obligatorio',
            'price.numeric' => 'El precio debe ser un número válido',
            'price.min' => 'El precio no puede ser negativo',
            'stock.required' => 'El stock es obligatorio',
            'stock.integer' => 'El stock debe ser un número entero',
            'stock.min' => 'El stock no puede ser negativo',
            'description.required' => 'La descripción es obligatoria',
            'description.string' => 'La descripción debe ser texto válido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $product = $store->products()->create($validator->validated());

            return response()->json([
                'status' => 'success',
                'data' => $product,
                'message' => 'Producto creado correctamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store, Product $product)
    {
        if ($product->store_id !== $store->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'El producto no pertenece a esta tienda.'
            ], 404);
        }

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Store $store, Product $product)
    {
        if ($product->store_id !== $store->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'El producto no pertenece a esta tienda'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'description' => 'sometimes|string|min:1'
        ], [
            'name.string' => 'El nombre debe ser texto válido',
            'name.max' => 'El nombre no puede exceder 255 caracteres',
            'price.numeric' => 'El precio debe ser un número válido',
            'price.min' => 'El precio no puede ser negativo',
            'stock.integer' => 'El stock debe ser un número entero',
            'stock.min' => 'El stock no puede ser negativo',
            'description.min' => 'La descripción no puede estar vacía',
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
            $product->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'data' => $product,
                'message' => 'Producto actualizado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store, Product $product)
    {
        if ($product->store_id !== $store->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'El producto no pertenece a esta tienda'
            ], 404);
        }

        try {
            $product->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Producto eliminado correctamente',
                'deleted_id' => $product->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
