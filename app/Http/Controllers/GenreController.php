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
        return ResponseController::success();
    }
    public function update(Genre $genre,Request $request){
        try{
            $this->authorize('update',Genre::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $name = $request->name;
        if(!$genre){
            return ResponseController::error('Genre not found',404);
        }
        $genre->update([
            'name'=>$name
        ]);
        return ResponseController::success();
    }
    public function delete (Genre $genre){
        try{
            $this->authorize('delete',Genre::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
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
        $devs = Genre::onlyTrashed()->get();
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
        Genre::withTrashed()->where('id',$id)->restore();
        GenreProduct::withTrashed()->where('genre_id',$id)->restore();
        return ResponseController::success('successful',200);
    }

    public function all(){
        $data = Genre::all();
        if(count($data)==0){
            return ResponseController::error('No genres yet',404);
        }
        foreach ($data as $genre){
            $products = $genre->genreproduct()->count();
            $genre['products'] = $products;
        }
        return ResponseController::data($data);
    }
}
