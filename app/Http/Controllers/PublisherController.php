<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Publisher;

class PublisherController extends Controller
{
    public function Create(Request $request){
        try{
            $this->authorize('create',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $name = $request->name;
        $publisher = Publisher::where('name',$name)->first();
        if($publisher){
            return ResponseController::error('This genre already exists');
        }
        $publisher= Publisher::create([
            'name'=>$name
        ]);
        return ResponseController::success();
    }
    public function update(Request $request,Publisher $publisher){
        try{
            $this->authorize('update',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $name = $request->name;
        $publisher->update([
            'name'=>$name
        ]);
        return ResponseController::success();
    }
    public function delete (Publisher $publisher){
        try{
            $this->authorize('delete',Publisher::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
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
        Publisher::withTrashed()->where('id',$id)->restore();
        Product::withTrashed()->where('publisher_id',$id)->restore();
        return ResponseController::success();
    }

    public function all(){
        $data = Publisher::all();
        if(count($data)==0){
            return ResponseController::error('No publishers yet');
        }
        foreach($data as $publisher){
            $publisher['products'] = $publisher->products()->count();
        }
        return ResponseController::data($data);
    }
}