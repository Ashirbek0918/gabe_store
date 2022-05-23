<?php

use App\Models\Developer;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_img');
            $table->double('rating');
            $table->double('first_price');
            $table->double('discount')->nullable();
            $table->double('second_price');
            $table->mediumText('about');
            $table->json('minimal_system');
            $table->json('recommend_system');
            $table->boolean('warn');
            $table->text('warn_text');
            $table->json('screenshots');
            $table->json('trailers');
            $table->string('language');
            $table->string('region_activasion');
            $table->foreignIdFor(Publisher::class);
            $table->foreignIdFor(Developer::class);
            $table->json('relaease');
            $table->string('platform');
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
