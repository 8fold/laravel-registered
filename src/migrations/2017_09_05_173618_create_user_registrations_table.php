<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {      
        /** 2.0 */
        Schema::create('user_registrations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->dateTime('registered_on')
                ->comment("Date of when user registered for the site.");

            $table->dateTime('confirmed_on')->nullable()
                ->comment("When the user confirmed their registration.");

            $table->text('token')
                ->comment("Used in email for user to confirm registration.");

            $table->string('first_name')
                ->nullable()
                ->comment = "The desired first name of the user.";

            $table->text('last_name')
                ->nullable()
                ->comment = "The desired last name of the user.";

            $table->integer('user_id')
                ->unsigned()
                ->nullable()
                ->comment("The registered user.");
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('user_registrations');
    }
}
