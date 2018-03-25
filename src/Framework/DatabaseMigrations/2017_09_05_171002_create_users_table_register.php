<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTableRegister extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $noTable = !Schema::hasTable('users');
        if ($noTable) {
            // Create Laravel base users table
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();

                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('public_key')->nullable();
                $table->rememberToken();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Don't ever drop users table.
    }
}
