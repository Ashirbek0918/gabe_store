<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Comment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function comments(News $news){
        $comments = $news->comments;
        if (empty($comments)){
            return ResponseController::error('No comments in this news');
        } 
        return ResponseController::data($comments,200);
    }

    public function onenews(News $news){
        $new =[];
        $comments = $news->comments()->count();
        $new = [
            'title'=>$news->title,
            'image'=>$news->image,
            'body'=>$news->body,
            'created_at' =>$news->created_at,
            'comments'=>$comments
        ];
        $news->increment('views');
        return ResponseController::data($new);
    }

    public function create(Request $request){
        try{
            $this->authorize('create',News::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'title' =>'required|string',
            'image' =>'required|url',
            'body' =>'required',
            'text' =>'nullable|string'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        News::create([
            'title'=>$request->title,
            'image'=> $request->image,
            'body'=>$request->body,
            'text'=>$request->text
        ]);
        return ResponseController::success('Succesfuly',201);
    }

    public function all(){
        $news = News::orderBy('id','Desc')->paginate(10);
        if(!empty($news)){
            return ResponseController::error('Not News yet');
        }
        $final = [
            'last_page' =>$news->lastPage(),
            'news' => []
        ];
        foreach ($news as $item){
            $final['news'][] = [
                'id' =>$item->id,
                'title' =>$item->title,
                'image' =>$item->image,
                'views' =>$item->views,
                'created_at' =>$item->created_at,
                'comments' =>$item->comments()->count()
            ];
        }
        return ResponseController::data($final);
    }

   public function destroy($news){
        try{
            $this->authorize('delete',News::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $news = News::find($news);
        if(!$news){
            return ResponseController::error('News not found',404);
        }
        $news->comments()->delete();
        $news->delete();
        return ResponseController::success();
    }

    public function archive(){
        try{
            $this->authorize('view',News::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $news = News::onlyTrashed()->get();
        if(count($news)==0){
            return ResponseController::error('No deleted news',404);
        }
        return ResponseController::data($news);
    }

    public function restore(Request $request){
        try{
            $this->authorize('restore',News::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $id = $request->id;
        News::withTrashed()->where('id',$id)->restore();
        Comment::withTrashed()->where('news_id')->restore();
        return ResponseController::success('successful',200);
    }

    public function update(News $news,Request $request){
        try{
            $this->authorize('update',News::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        if(!$news){
            return ResponseController::error('News not found',404);
        }
        $validation = Validator::make($request->all(),[
            'title' =>'required|string',
            'image' =>'required|url',
            'body' =>'required',
            'text' =>'nullable|string'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $news->update($request->all());
        return ResponseController::success('successful',200);
    }
}
