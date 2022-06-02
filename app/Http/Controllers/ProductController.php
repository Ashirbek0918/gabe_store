<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Developer;
use App\Models\Publisher;
use App\Models\GenreProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class ProductController extends Controller
{
    
    public function create(Request $request)
    {
        try{
            $this->authorize('create',Product::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $validation = Validator::make($request->all(),[
            'title' =>'required|unique:products,title|max:50',
            'title_img' =>'required|url',
            'rating' =>'required|numeric',
            'first_price' =>'required|numeric',
            'discount' =>'nullable|numeric',
            'about' =>'nullable|string',
            'minimal_system' =>'nullable|',
            'recommend_system' =>'nullable|',
            'warn' =>'required|boolean',
            'warn_text' =>'nullable|',
            'screenshots' =>'required|',
            'trailers' =>'required|',
            'language' =>'required|string|max:255',
            'region_activasion' =>'nullable|string|max:255',
            'publisher_id' =>'required|exists:publishers,id',
            'developer_id' =>'required|exists:developers,id',
            'genre_id' =>'required|exists:genres,id',
            'platform' =>'required|string',
            'relaease' =>'required|'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $first_price = $request->first_price;
        $discaunt = $request->discount ?? 0;
        $second_price = ($first_price - ($first_price * $discaunt / 100));
        $product = Product::where('title', $request->title)->first();
        $genre_ids = $request->genre_id;
        if ($product) {
            return ResponseController::error('This product already exits');
        }
        $product = Product::create([
            'title' => $request->title,
            'title_img' => $request->title_img,
            'rating' => $request->rating,
            'first_price' => $request->first_price,
            'discount' => $request->discount,
            'second_price' => $second_price,
            'about' => $request->about,
            'minimal_system' => $request->minimal_system,
            'recommend_system' => $request->recommend_system,
            'warn' => $request->warn,
            'warn_text' => $request->warn_text,
            'screenshots' => $request->screenshots,
            'trailers' => $request->trailers,
            'language' => $request->language,
            'region_activasion' => $request->region_activasion,
            'publisher_id' => $request->publisher_id,
            'developer_id' => $request->developer_id,
            'platform' => $request->platform,
            'relaease' => $request->relaease
        ]);
        foreach ($genre_ids as $item) {
            GenreProduct::create([
                'genre_id' => $item,
                'product_id' => $product->id
            ]);
        }
        return ResponseController::success('Product succesfuly created',201);
    }

    public function all(Request $request)
    {
        $orderBy = $request->orderBy; // expensive inexpensive alphabet date popular
        $genre = $request->genre; // 1|2|3
        $price = $request->price;

        $products = Product::when($orderBy, function($query, $orderBy){
            if($orderBy == 'expensive'){
                $query->orderBy('first_price', 'desc');
            }elseif($orderBy == 'inexpensive'){
                $query->orderBy('first_price', 'asc');
            }elseif($orderBy == 'alphabet'){
                $query->orderBy('title', 'asc');
            }elseif($orderBy == 'date'){
                $query->orderBy('created_at', 'desc');
            }elseif($orderBy == 'popular'){
                $query->orderBy('buy_count', 'desc');
            }
        });

        if($genre){
            $products = $products->whereHas('genre', function($query) use ($genre){
                $query->whereIn('genre_id', explode('|', $genre));
            });
        }

        $products = $products->paginate(15);
        if (!$products) {
            return ResponseController::error(' No Products yet', 404);
        }
        $final = [
            'last_page'=> $products->lastPage(),
            'products' => [],
        ];
        foreach ($products as $item) {
            $final['products'][]= [
                'id' =>$item->id,
                'title' => $item->title,
                'title_img' => $item->title_img,
                'rating' => $item->rating,
                'first_price' => $item->first_price,
                'discount' => $item->discount,
                'second_price' => $item->second_price,
            ];
        }
        return ResponseController::data($final);
    }

    public function genre_id(Genre $genre)
    {
        $genre_id = $genre->id;
        $product_ids = GenreProduct::where('genre_id', $genre_id)->get('product_id');
        $products = [];
        if (!$product_ids) {
            return ResponseController::error(' No Products yet');
        }
        foreach ($product_ids as $item) {
            $product = Product::where('id', $item['product_id'])->first();
            $products[] = $product;
        }

        return ResponseController::data($products);
    }

    public function developer_id($developer_id)
    {
        $developer = Developer::find($developer_id);
        if(! $developer){
            return ResponseController::error('Developer not found',404);
        }
        $products = $developer->products()->get(['id','title','title_img','first_price','discount','second_price']);
        if (!$products) {
            return ResponseController::error(' No Products yet');
        }
        $developer['products'] = $products;
        return ResponseController::data($developer);
    }

    public function publisher_id($publisher_id)
    {
        $publisher = Publisher::find($publisher_id);
        if(! $publisher){
            return ResponseController::error('Publisher not found',404 );
        }
        $products = $publisher->products()->get(['id','title','title_img','first_price','discount','second_price']);
        if (!$products) {
            return ResponseController::error(' No Products yet');
        }
        $publisher['products'] = $products;
        return ResponseController::data($publisher);
    }

    public function destroy(Product $product)
    {
        try{
            $this->authorize('delete',Product::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        Comment::where('product_id', $product->id)->delete();
        $product->delete();
        return ResponseController::success();
    }

    public function archive()
    {
        try{
            $this->authorize('view',Product::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $products = Product::onlyTrashed()->get(['id','title','title_img','first_price','discount','second_price']);
        if (count($products) == 0) {
            return ResponseController::error('No deleted products', 404);
        }
        return ResponseController::data($products);
    }

    public function restore(Request $request)
    {
        try{
            $this->authorize('restore',Product::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $id = $request->id;
        $product = Product::onlyTrashed()->where('id', $id)->first();
        if(!$product){
            return ResponseController::error('Deleted product not found',404);
        }
        $product->restore();
        Comment::withTrashed()->where('product_id', $id)->restore();
        return ResponseController::success('successful', 200);
    }

    public function update(Product $product, Request $request)
    {
        try{
            $this->authorize('update',Product::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        if (!$product) {
            return ResponseController::error('Product not found', 404);
        }
        $validation = Validator::make($request->all(),[
            'title' =>'required|unique:products,title|max:50',
            'title_img' =>'required|url',
            'rating' =>'required|numeric',
            'first_price' =>'required|numeric',
            'discount' =>'nullable|numeric',
            'about' =>'nullable|string',
            'minimal_system' =>'nullable|',
            'recommend_system' =>'nullable|',
            'warn' =>'required|boolean',
            'warn_text' =>'nullable|',
            'screenshots' =>'required|',
            'trailers' =>'required|',
            'language' =>'required|string|max:255',
            'region_activasion' =>'nullable|string|max:255',
            'publisher_id' =>'required|exists:publishers,id',
            'developer_id' =>'required|exists:developers,id',
            'genre_id' =>'required|exists:genres,id',
            'platform' =>'required|string',
            'relaease' =>'required|'
        ]);
        if ($validation->fails()) {
            return ResponseController::error($validation->errors()->first(), 422);
        }
        $product->update($request->all());
        return ResponseController::success('successful', 200);
    }

    public function product_comments(Product $product){
        $comments = $product->comments()->paginate(30) ;
        if(count($comments) == 0){
            return ResponseController::error('No comments yet');
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

    public function product (Product $product){
        $comments= $product->comments()->count();
        $temp = [];
        $final = [];
        $temp = [
            'title' => $product->title,
            'title_img' => $product->title_img,
            'rating' => $product->rating,
            'first_price' => $product->first_price,
            'discount' => $product->discount,
            'second_price' => $product->second_price,
            'about' => $product->about,
            'minimal_system' => $product->minimal_system,
            'recommend_system' => $product->recommend_system,
            'warn' => $product->warn,
            'warn_text' => $product->warn_text,
            'screenshots' => $product->screenshots,
            'trailers' => $product->trailers,
            'language' => $product->language,
            'region_activasion' => $product->region_activasion,
            'publisher_id' => $product->publisher_id,
            'developer_id' => $product->developer_id,
            'platform' => $product->platform,
            'relaease' => $product->relaease,
            'comments' =>$comments
        ];
        foreach ($product->genre as $item) {
            $genre = Genre::where('id', $item->genre_id)->first(['id', 'name']);
            if ($genre) {
                $temp['genre'][] = $genre;
            }
        }
        $final = $temp;
        return ResponseController::data($final);
    }

    public function latestAdd(){
        $products = Product::orderBy('created_at','Desc')->take(10)->get([
            'id','title','title_img','first_price','discount','second_price'
        ]);
        return ResponseController::data($products);
    }

    public function orderByDiscount(){
        $products = Product::orderBy('discount','Desc')->take(10)->get([
            'id','title','title_img','first_price','discount','second_price'
        ]);
        return ResponseController::data($products);
    }

    public function orderByRating(){
        $products = Product::orderBy('rating','Desc')->take(10)->get([
            'id','title','title_img','rating','first_price','discount','second_price'
        ]);
        return ResponseController::data($products);
    }

    public function orderBycount(){
        $products = Product::orderBy('buy_count','Desc')->take(10)->get([
            'id','title','title_img','buy_count','first_price','discount','second_price'
        ]);
        return ResponseController::data($products);
    }
}
