<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    public function upload (Request $request){
        $validation  = Validator::make($request->all(),[
            'images' =>'required'
        ]);
        if ($validation->fails()) { 
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $image_url = [];
        $images = $request->file('images');
        if(!is_array($images)){
            $image_name = time()."_".Str::random(10).".".$images->getClientOriginalExtension();
            $images->move('Images',$image_name);
            $image_url[] = env('APP_URL')."/images/".$image_name;
        }
        foreach($images as $image){
            $image_name = time()."_".Str::random(10).".".$image->getClientOriginalExtension();
            $image->move('Images',$image_name);
            $image_url[] = env('APP_URL')."/images/".$image_name;
        }
        return $image_url;
    }
    public function destroy($fileName)
    {
        $path = public_path('/images/'.$fileName);
        if (!$path) {
            return ResponseController::error('Image does not exist');
        }
        File::delete($path);
        return ResponseController::success('Image has been successfully deleted');
    }
}
