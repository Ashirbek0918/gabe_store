<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenreProduct extends Model
{
    use HasFactory ,SoftDeletes;
    protected $fillable = [
        'genre_id','product_id'
    ];
    public function genre()
    {
        return $this->hasOne(Genre::class, 'id');
    }

    public function products(){
        return $this->belongsToMany(GenreProduct::class, Product::class,'product_id','id');
    }
    
}
