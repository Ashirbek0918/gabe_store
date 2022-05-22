<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Product;
use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    public function Create(Request $request){
        try{
            $this->authorize('create',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $name = $request->name;
        $developer = Developer::where('name',$name)->first();
        if($developer){
            return ResponseController::error('This developer already exists');
        }
        $developer= Developer::create([
            'name'=>$name
        ]);
        return ResponseController::success();
    }
    public function update(Request $request,Developer $developer){
        try{
            $this->authorize('update',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $name = $request->name;
        $developer->update([
            'name'=>$name
        ]);
        return ResponseController::success();
    }
    public function delete (Developer $developer){
        try{
            $this->authorize('delete',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $developer->products()->delete();
        $developer->delete();
        return ResponseController::success();
    }
    public function archive(){
        try{
            $this->authorize('view',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $devs = Developer::onlyTrashed()->get();
        if(count($devs)==0){
            return ResponseController::error('No deleted products');
        }
        return ResponseController::data($devs);
    }
    public function restore(Request $request){
        try{
            $this->authorize('restore',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $id = $request->id;
        Developer::withTrashed()->where('id',$id)->restore();
        Product::withTrashed()->where('developer_id',$id)->restore();
        return ResponseController::success();
    }
    public function all(){
        $data = Developer::all();
        if(count($data)==0){
            return ResponseController::error('No developers yet');
        }
        foreach ($data as $developer){
            $count = $developer->products()->count();
            $developer['products']= $count;
        }
        return ResponseController::data($data);
    }
    
    
}
