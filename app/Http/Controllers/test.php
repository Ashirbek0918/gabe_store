<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Developer;
use App\Models\Genre;
use App\Models\GenreProduct;
use App\Models\Product;
use App\Models\Publisher;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    
    public function create(Request $request)
    {
        try{
            $this->authorize('create',Product::class);
        }catch(\Throwable $th){
            return ResponseController::error('You Are not allowed');
        }
        $first_price = $request->first_price;
        $discaunt = $request->discount;
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
        $final = [];
        foreach ($products as $product) {
            $comments = $product->comments()->count();
            $data = $product;
            $data['comments'] = $comments;
            $data['genre_product'] = $product->genreProduct;
            $final[] = collect($data)->except('genre');
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
        $product['comments'] = $product->comments()->count();
        foreach ($product->genre as $item) {
            $genre = Genre::where('id', $item->genre_id)->first(['id', 'name']);
            if ($genre) {
                $temp['genre'][] = $genre;
            }
        }
        $final = $temp;
        return ResponseController::data($final);
    }
}
