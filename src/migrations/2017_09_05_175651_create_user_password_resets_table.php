<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {     
        Schema::dropIfExists('password_resets');
        
        Schema::create('user_password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('token');
            $table->string('code');            

            $table->integer('user_registration_id')
                ->unsigned()
                ->comment('The user registration for the user requesting reset.');
            $table->foreign('user_registration_id')
                ->references('id')
                ->on('user_registrations')
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
        Schema::dropIfExists('user_password_resets');
    }
}
