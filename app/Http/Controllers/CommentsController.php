<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\News;
use GuzzleHttp\Psr7\Response;

class CommentsController extends Controller
{
    public function create(Request $request){
        $product_id = $request->product_id ?? null;
        $news_id = $request->news_id ?? null;
        Comment::create([
            'title' =>$request->title,
            'user_id' =>$request->user_id,
            'product_id'=>$product_id,
            'news_id'=>$news_id
        ]);
        return ResponseController::success('successful',200);
    }

    public function all_products_comments(){
        try{
            $this->authorize('view',Comment::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $comments = Comment::whereNotNull('product_id')->get();
        if(!$comments){
            return ResponseController::error('No comments yet',404);
        }
        return ResponseController::data($comments);
    }

    public function all_news_comments(){
        try{
            $this->authorize('view',Comment::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $comments = Comment::whereNotNull('news_id')->get();
        if(!$comments){
            return ResponseController::error('No comments yet',404);
        }
        $collect = collect($comments);
        $collect = $collect->sortBy('created_at');
        return ResponseController::data($collect);
    }

    public function destroy(Comment $comment){
        $comment->delete();
        return ResponseController::success();
    }

    public function archive(){
        try{
            $this->authorize('view',Comment::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $comments = Comment::onlyTrashed()->get();
        if(count($comments)==0){
            return ResponseController::error('No deleted comments',404);
        }
        return ResponseController::data($comments);
    }

    public function restore(Request $request){
        $id = $request->comment_id;
        $comment = Comment::withTrashed()->where('id',$id)->restore();
        if(!$comment){
            return ResponseController::error('comment not found');
        }
        return ResponseController::success('successful',200);
    }

    public function update(Request $request){
        $id = $request->comment_id;
        $comment = Comment::find($id);
        if(!$comment){
            return ResponseController::error('Comment not found',404);
        }
        $comment->update([
            'title'=>$request->title
        ]);
        return ResponseController::success('successful',200);
    }

    public function add_point(Request $request){
        try{
            $this->authorize('update',Comment::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $comment_id = $request->comment_id;
        $comment = Comment::find($comment_id);
        if($comment->status == 'unchecked'){
            $user_id = $request->user_id;
            $user = User::find($user_id);
            $user->update([
                'point'=> $user->point+5
            ]);
            $comment->update([
                'status'=> 'checked'
            ]);
            return ResponseController::success();
        }else{
            return ResponseController::error('error');
        }
    }
}
