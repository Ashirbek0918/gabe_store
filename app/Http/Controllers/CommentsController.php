<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller
{
    public function create(Request $request){
        $product_id = $request->product_id ?? null;
        $news_id = $request->news_id ?? null;
        $validation = Validator::make($request->all(),[
            'title' =>'required',
            'user_id' =>'required|exists:users,id',
            'product_id' =>'nullable|exists:products,id',
            'news_id' =>'nullable|exists:news,id'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        Comment::create([
            'title' =>$request->title,
            'user_id' =>$request->user_id,
            'product_id'=>$product_id,
            'news_id'=>$news_id,
        ]);
        return ResponseController::success('successful',201);
    }

    public function all_products_comments(){
        try{
            $this->authorize('view',Comment::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $comments = Comment::whereNotNull('product_id')->where('status','unchecked')->orderBy('id','Desc')->paginate(30);
        if(empty($comments)){
            return ResponseController::error('No comments yet',404);
        }
        $final = [
            'last_page'=> $comments->lastPage(),
            'comments'=> [],
        ];
        foreach ($comments as $comment){
            $final['comments'][] = [
                'id'=> $comment->id,
                'title' =>$comment->title,
                'user' => [
                    'id' =>$comment->user->id,
                    'name' =>$comment->user->name,
                ],
                'created_at' =>$comment->created_at,
            ];
        }
        return ResponseController::data($final,200);
    }

    public function all_news_comments(){
        try{
            $this->authorize('view',Comment::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $comments = Comment::whereNotNull('news_id')->orderBy('id','Desc')->paginate(30);
        if(empty($comments)){
            return ResponseController::error('No comments yet',404);
        }
        $final = [
            'last_page' =>$comments->lastPage(),
            'comments' => []
        ];
        foreach ($comments as $comment){
            $final['comments'][] = [
                'id'=> $comment->id,
                'title' =>$comment->title,
                'user' => [
                    'id' =>$comment->user->id,
                    'name' =>$comment->user->name,
                ],
                'created_at' =>$comment->created_at,
            ];
        }
        return ResponseController::data($final);
    }

    public function destroy($comment){
        $comment = Comment::find($comment);
        if(!$comment){
            return ResponseController::error('Comment not found',404);
        }
        $comment->delete();
        return ResponseController::success();
    }
    
    public function archive(){
        try{
            $this->authorize('view',Comment::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $comments = Comment::onlyTrashed()->orderBy('deleted_at','Desc')->get();
        if(count($comments)==0){
            return ResponseController::error('No deleted comments',404);
        }
        return ResponseController::data($comments);
    }

    public function restore(Request $request){
        $id = $request->comment_id;
        $comment = Comment::onlyTrashed()->where('id',$id)->first();
        if(!$comment){
            return ResponseController::error('comment not found');
        }
        $comment->restore();
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
            return ResponseController::error('Comment already checked');
        }
    }
}
