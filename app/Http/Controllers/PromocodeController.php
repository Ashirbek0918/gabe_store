<?php

namespace App\Http\Controllers;

use App\Models\Promocode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromocodeController extends Controller
{
    public function create(Request $request){
        try{
            $this->authorize('create',Promocode::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'promocode' =>'required|unique:promocodes,promocode|string|max:255',
            'discount' =>'required|numeric',
            'count' =>'nullable|numeric'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $count = $request->count ?? 0;
        Promocode::create([
            'promocode' =>$request->promocode,
            'discount' =>$request->discount,
            'count' =>$count
        ]);
        return ResponseController::success('Promocode succesfuly created',201);
    }
     
    public function allpromocodes(){
        try{
            $this->authorize('view',Promocode::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $promocodes = Promocode::orderBy('id','Desc')->paginate(30);
        if(!empty($promocodes)){
            return ResponseController::error('No promocodes yet', 404);
        }
        $final = [
            'last_page' =>$promocodes->lastPage(),
            'promocodes' => []
        ];
        foreach ($promocodes as $promocode){
            $final['promocodes'][] = [
                'id' =>$promocode->id,
                'promocode' =>$promocode->promocode,
                'discount' =>$promocode->discount,
                'count' =>$promocode->count,
                'created_at' =>$promocode->created_at,
            ];
        }
        return ResponseController::data($final);
    }

    public function delete($promocode_id){
        try{
            $this->authorize('delete',Promocode::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $promocode = Promocode::find($promocode_id);
        if(!$promocode){
            return ResponseController::error('Promocode not found', 404);
        }
        $promocode->delete();
        return ResponseController::success('Promocode succesfuly deleted');
    }

    public function update(Request $request,$promocode_id){
        try{
            $this->authorize('update',Promocode::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $promocode = Promocode::find($promocode_id);
        if(!$promocode){
            return ResponseController::error('Promocode not found', 404);
        }
        $validation = Validator::make($request->all(),[
            'promocode' =>'required|unique:promocodes,promocode|string|max:255',
            'discount' =>'required|numeric',
            'count' =>'nullable|numeric'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $promocode->update($request->all());
        return ResponseController::success('Promocode succesfuly updated');
    }
}
