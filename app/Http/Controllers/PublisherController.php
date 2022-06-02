<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublisherController extends Controller
{
    public function Create(Request $request){
        try{
            $this->authorize('create',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:publishers,name',
            'image' =>'required|url',
            'logo_img' =>'required|url',
            'description' =>'required|string'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        Publisher::create([
            'name'=>$request->name,
            'image' =>$request->image,
            'logo_img' =>$request->logo_img,
            'description' =>$request->description,
        ]);
        return ResponseController::success('Publisher succesfuly created',201);
    }
    
    public function update(Request $request,$publisher){
        try{
            $this->authorize('update',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $publisher = Publisher::find($publisher);
        if(!$publisher){
            return ResponseController::error('Publisher not found',404);
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:publishers,name',
            'image' =>'required|url',
            'logo_img' =>'required|url',
            'description' =>'required|string'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $publisher->update($request->all());
        return ResponseController::success();
    }

    public function delete ($publisher){
        try{
            $this->authorize('delete',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $publisher = Publisher::find($publisher);
        if(!$publisher){
            return ResponseController::error('Publisher not found',404);
        }
        $publisher->products()->delete();
        $publisher->delete();
        return ResponseController::success();
    }

    public function archive(){
        try{
            $this->authorize('view',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $devs = Publisher::onlyTrashed()->get();
        if(count($devs)==0){
            return ResponseController::error('No deleted publishers');
        }
        return ResponseController::data($devs);
    }

    public function restore(Request $request){
        try{
            $this->authorize('restore',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $id = $request->id;
        $publisher = Publisher::onlyTrashed()->where('id',$id)->first();
        if(!$publisher){
            return ResponseController::error('Deleted publisher not found',404);
        }
        $publisher->restore();
        Product::withTrashed()->where('publisher_id',$id)->restore();
        return ResponseController::success();
    }

    public function all(){
        $data = Publisher::get(['id','name','image','logo_img']);
        if(count($data)==0){
            return ResponseController::error('No publishers yet');
        }
        foreach($data as $publisher){
            $publisher['products'] = $publisher->products()->count();
        }
        return ResponseController::data($data);
    }
}
