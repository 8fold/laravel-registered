<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('public_key')
                ->nullable()
                ->comment('A random string to obfuscate the id field.');

            $table->string('email')
                ->unique()
                ->comment('Email address to send the invitation to.');

            $table->string('token')
                ->unique()
                ->comment('Token for the invitation - send via email.');

            $table->string('code')
                ->unique()
                ->comment('The code, or temporary password, for the invitation.');

            $table->dateTime('claimed_on')->nullable();

            $table->integer('inviter_registration_id')
                ->unsigned()
                ->nullable()
                ->comment('The registration of the user who sent the invitation.');
            $table->foreign('inviter_registration_id')
                ->references('id')
                ->on('user_registrations')
                ->onDelete('cascade')
                ->unsigned();

            $table->integer('user_registration_id')
                ->unsigned()
                ->nullable()
                ->comment('The registration for the invited user.');
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
        Schema::dropIfExists('user_invitations');
    }
}
