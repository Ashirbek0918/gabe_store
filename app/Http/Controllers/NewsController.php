<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function comments(News $news){
        $comments = $news->comments;
        if (count($comments) == null){
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
        News::create([
            'title'=>$request->title,
            'image'=> $request->image,
            'body'=>$request->body,
            'text'=>$request->text
        ]);
        return ResponseController::success();
    }

    public function all(){
        $news = News::all(['id', 'title','image', 'text','views', 'created_at']);
        if($news == null){
            return ResponseController::error('Not News yet');
        }
        return ResponseController::data($news);
    }

   public function destroy(News $news){
        try{
            $this->authorize('delete',News::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
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
        $news->update($request->all());
        return ResponseController::success('successful',200);
    }


}
