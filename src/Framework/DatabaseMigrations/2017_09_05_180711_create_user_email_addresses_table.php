<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserEmailAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {     
        Schema::create('user_email_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('email')
                ->unique()
                ->comment = 'The email address to store with the user.';

            $table->boolean('is_default')
                ->default(false)
                ->comment = 'Whether the user has marked this address as their default for system messages and notifications.';

            $table->integer('user_registration_id')
                ->unsigned()
                ->nullable();
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
        Schema::dropIfExists('user_email_addresses');
    }
}
