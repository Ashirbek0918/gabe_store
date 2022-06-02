<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Developer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeveloperController extends Controller
{
    public function Create(Request $request){
        try{
            $this->authorize('create',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:developers,name',
            'image' =>'required|url',
            'logo_img' =>'required|url',
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        Developer::create([
            'name'=>$request->name,
            'image' =>$request->image,
            'logo_img' =>$request->logo_img,
        ]);
        return ResponseController::success('Developer succesfuly created',201);
    }
    public function update($developer,Request $request){
        try{
            $this->authorize('update',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $developer = Developer::find($developer);
        if(!$developer){
            return ResponseController::error('Developer not found',404);
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:developers,name',
            'image' =>'required|url',
            'logo_img' =>'required|url',
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        
        $developer->update($request->all());
        return ResponseController::success();
    }
    public function delete ($developer){
        try{
            $this->authorize('delete',Developer::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $developer = Developer::find($developer);
        if(!$developer){
            return ResponseController::error('Developer not found',404);
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
        $developer = Developer::onlyTrashed()->where('id',$id)->first();
        if(!$developer){
            return ResponseController::error('Deleted developer not found',404);
        }
        $developer->restore();
        Product::withTrashed()->where('developer_id',$id)->restore();
        return ResponseController::success();
    }
    public function all(){
        $data = Developer::get(['id','image','logo_img']);
        if(empty($data)){
            return ResponseController::error('No developers yet');
        }
        foreach ($data as $developer){
            $count = $developer->products()->count();
            $developer['products']= $count;
        }
        return ResponseController::data($data);
    }
    
    
}
