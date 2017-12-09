<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateDatabase
 */
class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->integer('role_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();

            $table->primary(['role_id', 'user_id']);

            $table->foreign('role_id')->references('id')->on('roles')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->integer('value')->nullable();
            $table->integer('value2')->nullable();

            $table->timestamps();
        });

        Schema::create('user_seller', function (Blueprint $table) {
            $table->integer('seller_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();

            $table->primary(['seller_id', 'user_id']);

            $table->foreign('seller_id')->references('id')->on('sellers')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->integer('value')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('role_user');
        Schema::drop('user_seller');
        Schema::drop('users');
        Schema::drop('roles');
        Schema::drop('sellers');
    }
}
