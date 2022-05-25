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
        return ResponseController::success();
    }

    public function all()
    {
        $products = Product::all();
        if (count($products) == 0) {
            return ResponseController::error(' No Products yet', 404);
        }
        $temp = [];
        $final = [];
        foreach ($products as $item) {
            $comments = $item->comments()->count();
            $temp = [
                'title' => $item->title,
                'title_img' => $item->title_img,
                'rating' => $item->rating,
                'first_price' => $item->first_price,
                'discount' => $item->discount,
                'second_price' => $item->second_price,
                'about' => $item->about,
                'minimal_system' => $item->minimal_system,
                'recommend_system' => $item->recommend_system,
                'warn' => $item->warn,
                'warn_text' => $item->warn_text,
                'screenshots' => $item->screenshots,
                'trailers' => $item->trailers,
                'language' => $item->language,
                'region_activasion' => $item->region_activasion,
                'publisher_id' => $item->publisher_id,
                'developer_id' => $item->developer_id,
                'platform' => $item->platform,
                'relaease' => $item->relaease,
                'comments' =>$comments
            ];
            foreach ($item->genre as $item) {
                // return $item;
                $genre = Genre::where('id', $item->genre_id)->first(['id', 'name']);
                if ($genre) {
                    $temp['genre'][] = $genre;
                }
            }
            $final[] = $temp;
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

    public function developer_id(Developer $developer)
    {
        $products = $developer->products;
        if (!$products) {
            return ResponseController::error(' No Products yet');
        }
        return ResponseController::data($products);
    }

    public function publisher_id(Publisher $publisher)
    {
        $products = $publisher->products;
        if (!$products) {
            return ResponseController::error(' No Products yet');
        }
        return ResponseController::data($products);
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
        $products = Product::onlyTrashed()->get();
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
        Product::withTrashed()->where('id', $id)->restore();
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
        $product->update($request->all());
        return ResponseController::success('successful', 200);
    }

    public function product_comments(Product $product){
        $comments = $product->comments ;
        if(count($comments) == 0){
            return ResponseController::error('No comments yet');
        }
        return ResponseController::data($comments,200);
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

    public function latestadd(){
        $products = Product::orderBy('created_at','Desc')->get();
        return ResponseController::data($products);
    }
}
