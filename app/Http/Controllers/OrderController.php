<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request){
        $user = $request->user();
        $product_id = $request->product_id;
        $count = $request->count;
        $discount = $request->discount ?? 0;
        $basket = Basket::where('user_id',$user->id)->where('status','not purchased')->first();
        
        if(!$basket){
            $basket = Basket::create([
                'user_id'=>$user->id,
                'discount'=>$request->discount,
                'ordered_at'=>Carbon::now()
            ]);
            Order::create([
                'basket_id'=>$basket->id,
                'product_id'=>$product_id,
                'count'=>$count
            ]);
        }else{
            Order::create([
                'basket_id'=>$basket->id,
                'product_id'=>$product_id,
                'count'=>$count
            ]);
        }
        return ResponseController::success();
    }
    public function deleteorder($order_id){
        $order = Order::find($order_id);
        if(!$order){
            return ResponseController::error('Product not found',404);
        }
        $order->delete();
        return ResponseController::success('Order succesfuly deleted');
    }  
}

