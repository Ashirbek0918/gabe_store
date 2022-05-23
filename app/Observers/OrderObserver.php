<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */

    public function creating(Order $order){
        $product = $order->product;
        $order->activation_code = Str::uuid();
        $order->price = $product->first_price;
        $order->discount = $product->discount ?? 0;
        $order->discount_price = $product->second_price;
    }
    public function created(Order $order)
    {
        $basket = $order->basket;
        $price = ($order->discount_price)+$basket->price;
        $basket->update([
            'price'=> $price
        ]);
    }

    /**
     * Handle the Order "updated" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function updated(Order $order)
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function deleted(Order $order)
    {
        $basket = $order->basket;
        $price = ($basket->price) - $order->price;
        $basket->update([
            'price'=> $price,
        ]);

    }

    /**
     * Handle the Order "restored" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function restored(Order $order)
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function forceDeleted(Order $order)
    {
        //
    }
}
