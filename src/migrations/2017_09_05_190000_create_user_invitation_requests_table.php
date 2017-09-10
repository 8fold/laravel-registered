<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserInvitationRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_invitation_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('email');
            $table->integer('user_invitation_id')
                ->unsigned()
                ->nullable()
                ->comment('The invitation created for the new user.');
            $table->foreign('user_invitation_id')
                ->references('id')
                ->on('user_invitations')
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
        Schema::dropIfExists('user_invitation_requests');
    }
}
