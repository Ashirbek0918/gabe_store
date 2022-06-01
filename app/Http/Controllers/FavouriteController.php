<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FavouriteController extends Controller
{
    public function create(Request $request){
       $validation = Validator::make($request->all(),[
        'user_id' =>'required|exists:users,id',
        'product_id' =>'required|exists:products,id',
       ]);
       if ($validation->fails()) {
        return ResponseController::error($validation->errors()->first(), 422);
       }
       $favourite = Favourite::where('user_id',$request->user_id)->where('product_id',$request->product_id)->first();
       if($favourite){
        return ResponseController::error('This favourite product already exits');
       }
       Favourite::create([
        'user_id' =>$request->user_id,
        'product_id' =>$request->product_id
       ]);
       return ResponseController::success('Succesfuly created',200);
    }

    public function delete(Request $request,$product_id){
        $favourite = Favourite::where('user_id',$request->user()->id)->where('product_id',$product_id)->first();
        if(!$favourite){
            return ResponseController::error('Favourite product not found',404);
        }
        $favourite->delete();
        return ResponseController::success('Favourite product successfuly deleted',200);
    }

    public function favourites(Request $request){
        $favourites = $request->user()->favourites()->get();
        if(count($favourites) == 0){
            return ResponseController::error('Favourite products not yet',404);
        }
        $final = [];
        foreach ($favourites as $favourite){
            $product = $favourite->products->first();
            $temp =[
                'id' =>$product->id,
                'title' =>$product->title,
                'title_img' =>$product->title_img,
                'first_price' =>$product->first_price,
                'discount' =>$product->discount,
                'second_price' =>$product->second_price,
            ];
            $final[]= $temp;
        }
        return ResponseController::data($final);
    }
}

