<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function upload (Request $request){
        $image_url = [];
        $images = $request->file('images');
        if ($request->hasFile('images')){
            foreach($images as $image){
                $image_name = time()."_".Str::random(10).".".$image->getClientOriginalExtension();
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
