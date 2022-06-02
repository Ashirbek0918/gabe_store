<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Product;
use App\Models\GenreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GenreController extends Controller
{
    public function Create(Request $request){
        try{
            $this->authorize('create',Genre::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:genres,name'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        Genre::create([
            'name'=>$request->name
        ]);
        return ResponseController::success('Succesfuly',201);
    }
    
    public function update($genre,Request $request){
        try{
            $this->authorize('update',Genre::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $name = $request->name;
        $genre= Genre::find($genre);
        if(!$genre){
            return ResponseController::error('Genre not found',404);
        }
        $validation = Validator::make($request->all(),[
            'name' =>'required|unique:genres,name'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $genre->update([
            'name'=>$name
        ]);
        return ResponseController::success();
    }

    public function delete ($genre){
        try{
            $this->authorize('delete',Genre::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $genre = Genre::find($genre);
        if(!$genre){
            return ResponseController::error('Genre not found',404);
        }
        $genre->genreProduct()->delete();
        $genre->delete();
        return ResponseController::success();
    }

    public function archive(){
        try{
            $this->authorize('view',Genre::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $devs = Genre::onlyTrashed()->orderBy('deleted_at','Desc')->get();
        if(count($devs)==0){
            return ResponseController::error('No deleted genres');
        }
        return ResponseController::data($devs);
    }

    public function restore (Request $request){
        try{
            $this->authorize('restore',Genre::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $id = $request->id;
        $genre = Genre::onlyTrashed()->where('id',$id)->first();
        if(is_null($genre)){
            return ResponseController::error('Deleted genre not found',404);
        }
        $genre->restore();
        GenreProduct::withTrashed()->where('genre_id',$id)->restore();
        return ResponseController::success('successful',200);
    }

    public function all(){
        $data = Genre::all();
        if(count($data)==0){
            return ResponseController::error('No genres yet',404);
        }
        $final = [];
        foreach ($data as $genre){
            $products = $genre->genreproduct()->count();
            $final['genres'][]= [
                'id' =>$genre->id,
                'name' =>$genre->name,
                'products' =>$products
            ];
        }
        return ResponseController::data($final);
    }
}
