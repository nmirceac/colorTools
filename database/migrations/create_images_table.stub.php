<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hash', 32)->index();
            $table->string('name')->index();
            $table->string('type', 4)->index();
            $table->integer('size')->unsigned();
            $table->smallInteger('width')->unsigned();
            $table->smallInteger('height')->unsigned();
            $table->text('metadata');
            $table->text('exif');
            $table->text('colors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
