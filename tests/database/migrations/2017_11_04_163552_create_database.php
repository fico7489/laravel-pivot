<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateDatabase.
 */
class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->string('id');
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

        Schema::create('seller_user', function (Blueprint $table) {
            $table->string('seller_id')->index();
            $table->integer('user_id')->unsigned()->index();

            $table->primary(['seller_id', 'user_id']);

            $table->foreign('seller_id')->references('id')->on('sellers')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->integer('value')->nullable();

            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');

            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->unsignedInteger('tag_id');
            $table->unsignedInteger('taggable_id');
            $table->string('taggable_type');

            $table->integer('value')->nullable();
            $table->integer('value2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('role_user');
        Schema::drop('user_seller');
        Schema::drop('users');
        Schema::drop('roles');
        Schema::drop('sellers');
        Schema::drop('posts');
        Schema::drop('videos');
        Schema::drop('tags');
        Schema::drop('taggables');
    }
}
