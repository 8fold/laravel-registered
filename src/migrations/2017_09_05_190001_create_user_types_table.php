<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Eightfold\Registered\Models\UserType;

class CreateUserTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('slug');
            $table->string('display');
            $table->string('visible_to')
                ->default('all');
            $table->boolean('can_delete')
                ->default(true);
        });

        $type = UserType::create([
                'slug' => 'owners',
                'display' => 'Owners'
            ]);
        $type->can_delete = false;
        $type->save();

        $type = UserType::create([
                'slug' => 'users',
                'display' => 'Users'
            ]);
        $type->can_delete = false;
        $type->save();

        Schema::table('user_invitations', function (Blueprint $table) {
            $table->integer('user_type_id')
                ->unsigned()
                ->nullable()
                ->comment('The registration for the invited user.');
            $table->foreign('user_type_id')
                ->default(2)
                ->references('id')
                ->on('user_types')
                ->onDelete('cascade')
                ->unsigned();
        });

        Schema::table('user_registrations', function (Blueprint $table) {
            $table->integer('primary_user_type_id')
                ->unsigned()
                ->comment('The primary type of the user.');
            $table->foreign('primary_user_type_id')
                ->default(2)
                ->references('id')
                ->on('user_types')
                ->onDelete('cascade')
                ->unsigned();
        });

        // Pivot
        Schema::create('user_registration_user_type', function (Blueprint $table) {
            $table->integer('user_registration_id')
                ->unsigned()
                ->comment('The registration for the user.');
            $table->foreign('user_registration_id')
                ->references('id')
                ->on('user_registrations')
                ->onDelete('cascade')
                ->unsigned();

            $table->integer('user_type_id')
                ->unsigned()
                ->comment('The user type for the registration.');
            $table->foreign('user_type_id')
                ->references('id')
                ->on('user_types')
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
        Schema::table('user_invitations', function(Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropColumn('user_type_id');
        });

        Schema::table('user_registrations', function(Blueprint $table) {
            $table->dropForeign(['primary_user_type_id']);
            $table->dropColumn('primary_user_type_id');
        });

        Schema::table('user_registration_user_type', function(Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropForeign(['user_registration_id']);
        });
        Schema::dropIfExists('user_registration_user_type');
        Schema::dropIfExists('user_types');
    }
}
