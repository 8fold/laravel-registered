<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'name')) {
            Schema::table('users', function(Blueprint $table) {
                $table->string('name')->nullable()->change();
            });
        }

        if (Schema::hasColumn('users', 'email')) {
            Schema::table('users', function(Blueprint $table) {
                $table->string('email')->nullable()->change();
            });
        }

        if (Schema::hasColumn('users', 'password')) {
            Schema::table('users', function(Blueprint $table) {
                $table->string('password')->nullable()->change();
            });
        }

        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function(Blueprint $table) {
                $table->string('username')
                    ->unique()
                    ->comment = "Used to create the member page URI.";
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
        // Don't delete username column
    }
}
