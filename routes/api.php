<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BasketController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PromocodeController;
use Illuminate\Support\Facades\Route;



Route::post('/registration',[AuthController::class,'create']);
Route::post('/login',[AuthController::class,'login']);
Route::post('loginEmployee',[AuthController::class,'employeelogin']);

Route::middleware('auth:sanctum')->group(function() {
    //users
    Route::post('users/logout', [AuthController::class, 'logout']);
    Route::get('users',[AuthController::class,'allUsers']);
    Route::get('user/{user}',[AuthController::class,'singleUser']);
    Route::get('orderbyPoint',[AuthController::class,'orderbyPoint']);

    //order
    Route::post('order/create',[OrderController::class,'createOrder']);
    Route::delete('order/delete/{order}',[OrderController::class,'deleteorder']);
    Route::get('popular/products',[BasketController::class,'Max']);

    //basket
    Route::get('allbaskets',[BasketController::class,'AllBaskets']);
    Route::get('alluserbaskets',[BasketController::class,'userbaskets']);
    Route::get('basket/{basket}',[BasketController::class,'basket']);
    Route::delete('basket/delete/{basket}',[BasketController::class,'delete']);
    Route::put('basket/update/{basket}',[BasketController::class,'update']);
    Route::post('basket/pay/{basket}',[BasketController::class,'pay']);

    // employee
    Route::post('create/employee',[AuthController::class,'createemployee']);
    Route::delete('delete/employee',[AuthController::class,'destroyemployee']);
    Route::put('update/employee',[AuthController::class,'updateemployee']);
    Route::get('getme',[AuthController::class,'getme']);

    //image
    Route::post('upload/image',[ImageController::class,'upload']);
    Route::delete('image/delete/{image_name}',[ImageController::class,'destroy']);
    
    //genre
    Route::post('create/genre',[GenreController::class,'create']);
    Route::put('update_genre/{genre?}',[GenreController::class,'update']);
    Route::delete('delete_genre/{genre}',[GenreController::class,'delete']);
    Route::get('all/genres',[GenreController::class,'all']);
    Route::get('genre/archive',[GenreController::class,'archive']);
    Route::post('genre/restore',[GenreController::class,'restore']);
    
    
    //developer
    Route::controller( DeveloperController::class)->group(function () {
        Route::post('create/developer', 'create');
        Route::put('update_developer/{developer}', 'update');
        Route::delete('delete/{developer}', 'delete');
        Route::get('all/developers', 'all');
        Route::get('developers/archive', 'archive');
        Route::post('developers/restore', 'restore');
    });


    //publisher
    Route::controller( PublisherController::class)->group(function () {
        Route::post('create/publisher', 'create');
        Route::put('update_publisher/{publisher}', 'update');
        Route::delete('delete_publisher/{publisher}', 'delete');
        Route::get('all/publishers', 'all');
        Route::get('publishers/archive', 'archive');
        Route::post('publishers/restore', 'restore');
    });

    //products
    Route::post('create/product',[ProductController::class,'create']);
    Route::get('all/products',[ProductController::class,'all']);
    Route::get('fromgenre/{genre}',[ProductController::class,'genre_id']);
    Route::get('frompublisher/{publisher}',[ProductController::class,'publisher_id']);
    Route::get('fromdeveloper/{developer}',[ProductController::class,'developer_id']);
    Route::delete('product_delete/{product}',[ProductController::class,'destroy']);
    Route::get('products/archive',[ProductController::class,'archive']);
    Route::post('products/restore',[ProductController::class,'restore']);
    Route::put('product_update/{product}',[ProductController::class,'update']);
    Route::get('product/{product}/comments',[ProductController::class,'product_comments']);
    Route::get('product/{product}',[ProductController::class,'product']);
    Route::get('latestAdd',[ProductController::class,'latestAdd']);
    Route::get('orderByDiscount',[ProductController::class,'orderByDiscount']);
    Route::get('orderByRating',[ProductController::class,'orderByRating']);
    Route::get('orderBycount',[ProductController::class,'orderBycount']);

    //comments
    Route::post('create/comment',[CommentsController::class,'create']);
    Route::get('allproducts/comments',[CommentsController::class,'all_products_comments']);
    Route::get('allnews/comments',[CommentsController::class,'all_news_comments']);
    Route::delete('comment_delete/{comment}',[CommentsController::class,'destroy']);
    Route::get('comments/archive',[CommentsController::class,'archive']);
    Route::post('comment/restore',[CommentsController::class,'restore']);
    Route::put('comment/update',[CommentsController::class,'update']);
    Route::put('comment/addpoint',[CommentsController::class,'add_point']);

    //news
    Route::post('create/news',[NewsController::class,'create']);
    Route::get('news',[NewsController::class,'all']);
    Route::get('news/{news}/comments',[NewsController::class,'comments']);
    Route::get('news/{news}',[NewsController::class,'onenews']);
    Route::delete('delete/{news}',[NewsController::class,'destroy']);
    Route::get('archive/news',[NewsController::class,'archive']);
    Route::post('restore/news',[NewsController::class,'restore']);
    Route::put('news_update/{news}',[NewsController::class,'update']);

    //promocode
    Route::post('promocode/create',[PromocodeController::class,'create']);
    Route::get('all_promocodes',[PromocodeController::class,'allpromocodes']);
    Route::delete('promocode/delete/{promocode}',[PromocodeController::class,'delete']);
    Route::put('promocode/update/{promocode}',[PromocodeController::class,'update']);

    //favourites
    Route::post('favourite/create',[FavouriteController::class,'create']);
    Route::delete('favourite/delete/{product_id}',[FavouriteController::class,'delete']);
    Route::get('favourites',[FavouriteController::class,'favourites']);
});
