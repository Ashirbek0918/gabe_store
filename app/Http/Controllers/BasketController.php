<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Basket;
use Illuminate\Http\Request;

use function PHPUnit\Framework\returnSelf;

class BasketController extends Controller 
{
    public function AllBaskets(){
        $baskets = Basket::all();
        foreach ($baskets as $basket){
            $basket['orders'] = $basket->orders()->count();
        }
        return ResponseController::data($baskets);
    }

    public function Userbaskets(Request $request){
        $user_id = $request->user()->id;
        $basket = Basket::where('user_id', $user_id)->where('status','not purchased')->first();
        if (!$basket){
            return ResponseController::error('Baskets not yet',404);
        }
        $basket['orders'] = $basket->orders;
        return ResponseController::data($basket);
    }

    public function basket ($basket){
        $basket = Basket::find($basket);
        if(!$basket){
            return ResponseController::error('Basket not found',404);
        }
        $orders = $basket->orders;
        return ResponseController::data($orders);
    }

    public function delete($basket){
        $basket = Basket::find($basket);
        if(!$basket){
            return ResponseController::error('Basket not found',404);
        }
        $basket->orders()->delete();
        $basket->delete();
        return ResponseController::success('Basket deleted succesfuly');
    }

    public function update(Request $request,$basket){
        $basket = Basket::find($basket);
        if(!$basket){
            return ResponseController::error('Basket not found',404);
        }
        $basket->update($request->all());
        return ResponseController::success();
    }

    public function Max(){
        $products = Order::countBy('product_id')->all();
        // dd($products);
        return $products;
    }

    public function pay($basket_id){
        $basket = Basket::find($basket_id);
        if(!$basket){
            return ResponseController::error('Basket not found',404);
        }
        if($basket->status != 'purchased'){
            $basket->update([
                'status' =>'purchased'
            ]);
        }else{
            return ResponseController::error(' Basket already paid');
        }
        return ResponseController::success();
    }
}
