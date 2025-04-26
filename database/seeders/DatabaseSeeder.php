<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = User::factory()->count(2)->create(['role' => 'seller']);

        $sellers->each(function ($seller) {
            $store = Store::factory()->create(['user_id' => $seller->id]);

            Product::factory()->count(5)->create(['store_id' => $store->id]);
        });

        $clients = User::factory()->count(3)->create(['role' => 'client']);

        $clients->each(function ($client) {
            $pendingCart = Cart::factory()->create([
                'user_id' => $client->id,
                'status' => 'pending',
            ]);

            $products = Product::inRandomOrder()->take(3)->get();

            foreach ($products as $product) {
                CartItem::factory()->create([
                    'cart_id' => $pendingCart->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 2),
                ]);
            }

            $order = Order::factory()->create([
                'user_id' => $client->id,
                'total' => $products->sum(fn($p) => $p->price),
            ]);

            foreach ($products as $product) {
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price_at_purchase' => $product->price,
                    'quantity' => 1,
                ]);

                $product->decrement('stock', 1);
            }
        });
    }
}
