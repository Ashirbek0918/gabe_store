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
        if ($request->hasFile('images')){
            
            foreach($images as $image){
                if(!$image){
                    return ResponseController::error('image not found');
                }
                $image_name = time()."_".Str::random(10).".".$image->getClientOriginalExtension();
                return $image_name;
                $image->move('Images',$image_name);
                $image_url[] = env('APP_URL')."/images/".$image_name;
            }
        }else{
            return ResponseController::error('None uploaded file');
        }
        return $image_url;
    }
    public function destroy($fileName)
    {
        $path = storage_path('app/public/images/'.$fileName);
        if (!$path) {
            return ResponseController::error('Image does not exist');
        }
        File::delete($path);
        return ResponseController::success('Image has been successfully deleted');
    }
}
