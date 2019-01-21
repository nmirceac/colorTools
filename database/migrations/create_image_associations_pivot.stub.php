<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImageAssociationsPivot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_associations', function (Blueprint $table) {
            $table->integer('image_id')->unsigned()->index();
            $table->foreign('image_id')->references('id')->on('images')->onDelete('cascade');
            $table->integer('association_id')->unsigned()->index();
            $table->string('association_type', 24)->index();
            $table->tinyInteger('order')->unsigned()->index();
            $table->string('role', 32)->index();
            $table->text('details');
            $table->primary(['image_id', 'association_id', 'association_type'], 'image_associations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('image_associations');
    }
}
