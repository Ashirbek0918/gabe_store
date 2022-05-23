<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Basket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function createOrder(Request $request){
        $validation = Validator::make($request->all(),[
            'product_id' =>'required|exists:products,id',
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $user = $request->user();
        $product_id = $request->product_id;
        $baskets = $user->baskets;
        foreach ($baskets as $basket){
            $order = $basket->orders()->where('product_id',$product_id)->first();
            if($order){
                return ResponseController::error('This order already exits');
            }
        }
        $basket = $user->baskets()->where('status','not purchased')->first();
        if(!$basket){
            $basket = Basket::create([
                'user_id'=>$user->id,
                'ordered_at'=>Carbon::now()
            ]);
            Order::create([
                'basket_id'=>$basket->id,
                'product_id'=>$product_id
            ]);
        }else{
            Order::create([
                'basket_id'=>$basket->id,
                'product_id'=>$product_id
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

