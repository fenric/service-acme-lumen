<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUsers extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('phone', 16);
            $table->string('email', 128)->unique();
            $table->string('password', 255);
            $table->string('access_token', 32)->nullable();
            $table->dateTime('access_token_created_at')->nullable();
            $table->string('password_recovery_token', 32)->nullable();
            $table->dateTime('password_recovery_token_created_at')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('last_password_change_at')->nullable();
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
        Schema::drop('users');
    }
}
