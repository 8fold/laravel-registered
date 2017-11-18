<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_sites', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->text('address');
            $table->text('type');
            $table->string('public_key')->nullable();
            $table->integer('profile_id')->unsigned()->nullable();
            $table->foreign('profile_id')
                ->references('id')
                ->on('user_profiles')
                ->onDelete('cascade')
                ->unsigned();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_sites');
    }
}
