<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory ,SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'minimal_system'=> 'json',
        'recommend_system'=> 'json',
        'trailers'=> 'json',
        'screenshots'=> 'json',
        'relaease'=> 'json'
    ];
    public function genre()
    {
        return $this->hasMany(GenreProduct::class,'product_id');
    }
    public function genreProduct():Attribute
    {
        $data = [];
        foreach ($this->genre as $item) {
            $genre = Genre::where('id', $item->genre_id)->first(['id', 'name']);
            if ($genre) {
                $data[] = $genre;
            }
        }
        return  Attribute::make(
            get: fn () => $data,
        );
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }
}
