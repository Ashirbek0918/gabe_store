<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Basket;
use App\Models\Promocode;

use Illuminate\Http\Request;

class BasketController extends Controller 
{
    public function AllBaskets(){
        $baskets = Basket::paginate(30);
        $final = [
            'last_page'=> $baskets->lastPage(),
            'baskets'=> [],
        ];
        foreach ($baskets as $basket){
            $basket['orders'] = $basket->orders()->count();
            $final['baskets'][] = [
                'id'=> $basket->id,
                'user'=> [
                    'id'=> $basket->user_id,
                    'name'=> $basket->user->name,
                    'email'=> $basket->user->email,
                    'point' => $basket->user->point,
                ],
                'status'=> $basket->status,
                'price'=> $basket->price,
                'discount'=> $basket->discount,
                'discount_price'=> $basket->discount_price,
                'ordered_at' => $basket->ordered_at,
                'orders_count'=> $basket->orders,
            ];
        }
        return ResponseController::data($final);
    }

    public function userbaskets(Request $request){
        $user_id = $request->user()->id;
        $basket = Basket::where('user_id', $user_id)->where('status','not purchased')->first();
        if (!$basket){
            return ResponseController::error('Basket not yet',404);
        }
        $orders = $basket->orders;
        return ResponseController::data($orders);
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

    public function pay($basket_id, Request $request){
        $basket = Basket::find($basket_id);
        $user = $request->user();
        if(!$basket){
            return ResponseController::error('Basket not found',404);
        }
        try {
           if(!is_null($request->promocode)){
                $promocode = Promocode::where('promocode',$request->promocode)->firstOrFail();
                $discount = $promocode->discount;
                $discount_price = $basket->price - ($basket->price*$discount/100);
                $promocode->decrement('count');
                if($promocode->count == 0){
                    $promocode->delete();
                }
           }
        } catch (\Throwable $th) {
            return ResponseController::error('No such promocode is available or is outdated');
        }
        if($basket->status != 'purchased'){
            $user->update([
                'buy_games_number' =>$user->buy_games_number + $basket->orders()->count(),
                'point'=>$user->point + 25
            ]);
            $basket->update([
                'status' =>'purchased',
                'discount' =>$discount ?? 0,
                'discount_price' =>$discount_price ?? 0,
            ]);
        }else{
            return ResponseController::error('Basket already paid');
        }
        return ResponseController::data($basket->orders);
    }
}
