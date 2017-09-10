<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Eightfold\RegistrationManagementLaravel\Models\UserType;

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
            $table->string('same_as')
                ->nullable();
            $table->integer('order')
                ->default(0);
            $table->boolean('can_delete')
                ->default(true);
        });

        UserType::create([
                'slug' => 'owners',
                'display' => 'Owners',
                'can_delete' => 0,
                'visible_to' => 'owners'
            ]);

        UserType::create([
                'slug' => 'users',
                'display' => 'Users',
                'can_delete' => 0
            ]);        

        Schema::table('user_registrations', function (Blueprint $table) {
            $table->integer('user_type_id')
                ->default(2)
                ->unsigned()
                ->nullable()
                ->comment('The registration for the invited user.');
            $table->foreign('user_type_id')
                ->references('id')
                ->on('user_types')
                ->onDelete('cascade')
                ->unsigned();            
        });

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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_registrations', function(Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropColumn('user_type_id');
        });

        Schema::table('user_invitations', function(Blueprint $table) {
            $table->dropForeign(['user_type_id']);
            $table->dropColumn('user_type_id');
        });
        Schema::dropIfExists('user_types');
    }
}
